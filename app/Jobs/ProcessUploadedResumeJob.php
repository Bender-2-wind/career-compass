<?php

namespace App\Jobs;

use App\Events\ProfileUpdatedEvent;
use App\Models\User;
use App\Models\Profile;
use App\AiAgents\ResumeParserAgent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Throwable;

class ProcessUploadedResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 500;

    public bool $deleteWhenMissingModels = true;

    protected string $temporaryPdfPath;

    protected int $userId;

    public function __construct(string $temporaryPdfPath, int $userId)
    {
        $this->temporaryPdfPath = $temporaryPdfPath;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $logContext = ['user_id' => $this->userId, 'pdf_path' => $this->temporaryPdfPath];
        
        try {
            Log::info('Starting resume processing job', $logContext);

            $this->validatePrerequisites();

            $user = $this->loadUser();
            $profile = $this->loadOrCreateProfile($user);

            $resumeText = $this->extractTextFromPdf();

            $parsedData = $this->parseResumeWithAgent($resumeText);

            $this->updateProfileWithParsedData($profile, $parsedData);

            ProfileUpdatedEvent::dispatch($this->userId);

            Log::info('Resume processing completed successfully', $logContext);

        } catch (Exception $e) {
            Log::error('Resume processing failed', array_merge($logContext, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]));
            throw $e;
        } finally {
            $this->cleanupTemporaryFile();
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Resume processing job failed permanently', [
            'user_id' => $this->userId,
            'pdf_path' => $this->temporaryPdfPath,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        $this->cleanupTemporaryFile();
    }

    private function validatePrerequisites(): void
    {
        if (!file_exists($this->temporaryPdfPath)) {
            throw new Exception("Temporary PDF file not found at {$this->temporaryPdfPath}");
        }

        if (!is_readable($this->temporaryPdfPath)) {
            throw new Exception("Temporary PDF file is not readable at {$this->temporaryPdfPath}");
        }

        $fileSize = filesize($this->temporaryPdfPath);
        if ($fileSize === false || $fileSize === 0) {
            throw new Exception("Temporary PDF file is empty or corrupted");
        }

        if ($fileSize > 10 * 1024 * 1024) {
            throw new Exception("PDF file is too large (> 10MB)");
        }
    }

    private function loadUser(): User
    {
        $user = User::find($this->userId);
        
        if (!$user) {
            throw new Exception("User with ID {$this->userId} not found");
        }

        return $user;
    }

    private function loadOrCreateProfile(User $user): Profile
    {
        return Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'title' => null,
                'professional_summary' => null,
                'skills' => [],
                'work_experiences' => [],
                'education' => [],
                'certifications' => [],
                'contact_info' => []
            ]
        );
    }

    private function extractTextFromPdf(): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($this->temporaryPdfPath);
            $resumeText = $pdf->getText();

            if (empty(trim($resumeText))) {
                throw new Exception("No text content extracted from PDF");
            }

            Log::info("Successfully extracted text from PDF", [
                'user_id' => $this->userId,
                'text_length' => strlen($resumeText)
            ]);

            return $resumeText;

        } catch (Exception $e) {
            throw new Exception("Failed to extract text from PDF: " . $e->getMessage());
        }
    }

    private function parseResumeWithAgent(string $resumeText): array
    {
        try {
            $agent = ResumeParserAgent::for("resume_parser_{$this->userId}");
            $response = $agent->respond($resumeText);

            Log::debug("AI agent raw response", [
                'user_id' => $this->userId,
                'response_type' => gettype($response),
                'response' => $response
            ]);

            if (!is_array($response)) {
                throw new Exception("AI agent returned invalid response type: " . gettype($response));
            }

            if (empty($response)) {
                throw new Exception("AI agent returned empty response");
            }

            $this->validateParsedData($response);

            $cleanedData = $this->cleanAndNormalizeData($response);

            Log::info("Resume successfully parsed by AI agent", [
                'user_id' => $this->userId,
                'data_keys' => array_keys($cleanedData)
            ]);

            return $cleanedData;

        } catch (Exception $e) {
            throw new Exception("Failed to parse resume with AI agent: " . $e->getMessage());
        }
    }

    private function validateParsedData(array $data): void
    {
        $requiredKeys = ['title', 'professional_summary', 'skills', 'work_experiences', 'education'];
        
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new Exception("Missing required key in parsed data: {$key}");
            }
        }

        if (!is_array($data['skills'])) {
            throw new Exception("Skills must be an array");
        }

        if (!is_array($data['work_experiences'])) {
            throw new Exception("Work experiences must be an array");
        }

        if (!is_array($data['education'])) {
            throw new Exception("Education must be an array");
        }
    }

    private function cleanAndNormalizeData(array $data): array
    {
        $cleanData = [
            'title' => $this->sanitizeString($data['title'] ?? ''),
            'professional_summary' => $this->sanitizeString($data['professional_summary'] ?? ''),
            'skills' => $this->cleanSkills($data['skills'] ?? []),
            'work_experiences' => $this->cleanWorkExperiences($data['work_experiences'] ?? []),
            'education' => $this->cleanEducation($data['education'] ?? []),
            'certifications' => $this->cleanCertifications($data['certifications'] ?? []),
            'contact_info' => $this->cleanContactInfo($data['contact_info'] ?? [])
        ];

        return $cleanData;
    }

    private function cleanSkills(array $skills): array
    {
        return array_values(array_filter(array_map(function ($skill) {
            return $this->sanitizeString($skill);
        }, $skills)));
    }

    private function cleanWorkExperiences(array $experiences): array
    {
        return array_map(function ($experience) {
            return [
                'company' => $this->sanitizeString($experience['company'] ?? ''),
                'position' => $this->sanitizeString($experience['position'] ?? ''),
                'start_date' => $experience['start_date'] ?? null,
                'end_date' => $experience['end_date'] ?? null,
                'achievements' => $this->formatAchievements($experience['achievements'] ?? ''),
                'location' => $this->sanitizeString($experience['location'] ?? null)
            ];
        }, $experiences);
    }

    private function cleanEducation(array $education): array
    {
        return array_map(function ($edu) {
            return [
                'institution' => $this->sanitizeString($edu['institution'] ?? ''),
                'degree' => $this->sanitizeString($edu['degree'] ?? ''),
                'field_of_study' => $this->sanitizeString($edu['field_of_study'] ?? null),
                'graduation_date' => $edu['graduation_date'] ?? null,
                'gpa' => $this->sanitizeString($edu['gpa'] ?? null),
                'honors' => $this->sanitizeString($edu['honors'] ?? null),
                'location' => $this->sanitizeString($edu['location'] ?? null)
            ];
        }, $education);
    }

    private function cleanCertifications(array $certifications): array
    {
        return array_map(function ($cert) {
            return [
                'name' => $this->sanitizeString($cert['name'] ?? ''),
                'issuer' => $this->sanitizeString($cert['issuer'] ?? ''),
                'date_obtained' => $cert['date_obtained'] ?? null,
                'expiry_date' => $cert['expiry_date'] ?? null
            ];
        }, $certifications);
    }

    private function cleanContactInfo(array $contactInfo): array
    {
        return [
            'email' => $this->sanitizeString($contactInfo['email'] ?? null),
            'phone' => $this->sanitizeString($contactInfo['phone'] ?? null),
            'linkedin' => $this->sanitizeString($contactInfo['linkedin'] ?? null),
            'website' => $this->sanitizeString($contactInfo['website'] ?? null),
            'location' => $this->sanitizeString($contactInfo['location'] ?? null)
        ];
    }

    private function formatAchievements(string $achievements): string
    {
        if (empty($achievements)) {
            return '';
        }

        // Normalize various bullet characters to standard markdown
        $normalizedText = str_replace(
            ['●', '•', '◦', '▪', '▫', '‣'],
            '- ',
            $achievements
        );

        // Split by newlines and filter empty lines
        $lines = array_filter(array_map('trim', explode("\n", $normalizedText)));

        if (empty($lines)) {
            return $achievements;
        }

        $html = '<ul>';
        foreach ($lines as $line) {
            // Remove leading bullet characters
            $cleanLine = ltrim($line, '-*• ');
            if (!empty($cleanLine)) {
                $html .= '<li>' . htmlspecialchars($cleanLine, ENT_QUOTES, 'UTF-8') . '</li>';
            }
        }
        $html .= '</ul>';

        return $html;
    }

    private function sanitizeString(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $cleaned = trim($input);
        return $cleaned === '' ? null : $cleaned;
    }

    private function updateProfileWithParsedData(Profile $profile, array $parsedData): void
    {
        try {
            DB::transaction(function () use ($profile, $parsedData) {
                $profile->update($parsedData);
                
                Log::info("Profile updated successfully", [
                    'user_id' => $this->userId,
                    'profile_id' => $profile->id
                ]);
            });
        } catch (Exception $e) {
            throw new Exception("Failed to update profile: " . $e->getMessage());
        }
    }

    private function cleanupTemporaryFile(): void
    {
        if (file_exists($this->temporaryPdfPath)) {
            try {
                unlink($this->temporaryPdfPath);
                Log::info("Temporary PDF file deleted", [
                    'pdf_path' => $this->temporaryPdfPath
                ]);
            } catch (Exception $e) {
                Log::warning("Failed to delete temporary PDF file", [
                    'pdf_path' => $this->temporaryPdfPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
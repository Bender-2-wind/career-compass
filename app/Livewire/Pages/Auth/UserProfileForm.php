<?php

namespace App\Livewire\Pages\Auth;

use Spatie\Tags\Tag;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Livewire\BaseFormComponent;
use Illuminate\Support\Facades\Log;
use Filament\Support\Exceptions\Halt;
use App\Jobs\ProcessUploadedResumeJob;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UserProfileForm extends BaseFormComponent
{
    protected static string $view = 'livewire.pages.auth.user-profile-form';

    public ?array $data = [];
    
    public $user;
    
    public bool $resumeUploadedAndParsed = false; // Flag to control UI elements

    public function mount(): void
    {
        $this->user = $this->getUser(); 
        $this->loadAndFillFormData(); 
    }

    #[On('echo-private:App.Models.User.{user.id},.profile.updated')]
    public function refreshFormAfterProfileUpdate(array $event): void
    {
        Log::info("Echo event received in UserProfileForm", [  
            'event_data' => $event,  
            'current_user_id' => $this->user->id,  
            'timestamp' => now()  
        ]);  

        $this->loadAndFillFormData();

        Notification::make()
            ->title('Resume data loaded successfully!')
            ->body('Your profile has been populated with resume data. Please review and save.')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->resumeUploadSection(), 
                $this->profileDetailsSection(), 
            ])
            ->model($this->user) 
            ->statePath('data'); 
    }

    private function resumeUploadSection(): Section
    {
        return Section::make('Resume Upload')
            ->description('Upload your resume (PDF only) to automatically fill in your profile details. Your resume will not be permanently stored after parsing.')
            ->schema([
                FileUpload::make('resume_upload')
                    ->label('Upload Resume (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240) 
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        if ($state instanceof TemporaryUploadedFile) {
                            try {
                                $tempDir = storage_path('app/temp/resumes');
                                if (!is_dir($tempDir)) {
                                    mkdir($tempDir, 0755, true);
                                }
                                
                                $permanentTempPath = $tempDir . '/' . Str::uuid() . '.pdf';
                                
                                if (copy($state->getPathname(), $permanentTempPath)) {
                                    Notification::make()
                                        ->title('Resume upload successful! Parsing...')
                                        ->body('Your resume is being processed. Please wait for the form to update with extracted data.')
                                        ->success()
                                        ->send();

                                    ProcessUploadedResumeJob::dispatch($permanentTempPath, $this->user->id);
                                } else {
                                    throw new \Exception('Failed to copy temporary file');
                                }
                            } catch (\Exception $e) {
                                Log::error('Error processing resume upload: ' . $e->getMessage());
                                
                                Notification::make()
                                    ->title('Upload Error')
                                    ->body('There was an error processing your resume. Please try again.')
                                    ->danger()
                                    ->send();
                            }
                        }
                    })
                    ->helperText('PDF files only. Max 10MB. The resume will be deleted after processing.'),
            ])
            ->hidden(fn() => $this->resumeUploadedAndParsed); 
    }

    private function profileDetailsSection(): Section
    {
        return Section::make('Profile Details')
            ->description('These fields will be automatically filled after resume parsing. Review and save them.')
            ->schema([
                TextInput::make('title')->label('Professional Title'),
                TagsInput::make('skills')
                    ->label('Skills')
                    ->suggestions(function () {
                        return Tag::where('type', 'skills')->pluck('name')->toArray();
                    }),
                Textarea::make('professional_summary')->label('Professional Summary')->rows(5),
                Repeater::make('work_experiences')
                    ->label('Work Experiences')
                    ->schema([
                        TextInput::make('company')->required(),
                        TextInput::make('position')->required(),
                        DatePicker::make('start_date')->native(false)->required()->format('Y-m-d'),
                        DatePicker::make('end_date')->native(false)->format('Y-m-d'),
                        RichEditor::make('achievements')->columnSpanFull(),
                    ])
                    ->defaultItems(0) 
                    ->columns(2),
                Repeater::make('education')
                    ->label('Education')
                    ->schema([
                        TextInput::make('institution'),
                        TextInput::make('degree'),
                        TextInput::make('field_of_study'),
                        DatePicker::make('graduation_date')->native(false)->format('Y-m-d'),
                        Textarea::make('note')->rows(3)->columnSpanFull(),
                    ])
                    ->defaultItems(0)
                    ->columns(2),
            ])->columns(1);
    }

    private function loadAndFillFormData(): void
    {
        $profileData = [];
        
        if ($this->user->profile) {
            $profileData['title'] = $this->user->profile->title;
            $profileData['professional_summary'] = $this->user->profile->professional_summary;
            $profileData['skills'] = $this->user->profile->skills ?? []; 
            $profileData['work_experiences'] = $this->user->profile->work_experiences ?? []; 
            $profileData['education'] = $this->user->profile->education ?? []; 

            $hasContent = !empty($profileData['professional_summary']) || 
                         !empty($profileData['work_experiences']) || 
                         !empty($profileData['education']) ||
                         !empty($profileData['title']) ||
                         !empty($profileData['skills']);
                         
            $this->resumeUploadedAndParsed = $hasContent;
        } else {
            $this->resumeUploadedAndParsed = false;
        }

        $this->form->fill($profileData);
    }

    public function updateProfile(): void
    {
        try {
            $data = $this->form->getState();

            $profile = $this->user->profile()->firstOrCreate([]);

            $profile->fill($data);
            $profile->save();

            $hasContent = !empty($data['professional_summary']) || 
                         !empty($data['work_experiences']) || 
                         !empty($data['education']) ||
                         !empty($data['title']) ||
                         !empty($data['skills']);
                         
            $this->resumeUploadedAndParsed = $hasContent;

            Notification::make()
                ->title('Profile updated successfully')
                ->success()
                ->send();
                
        } catch (Halt $exception) {
            return;
        } catch (\Throwable $exception) {
            Log::error("Error saving profile for user {$this->user->id}: " . $exception->getMessage(), ['exception' => $exception]);
            
            Notification::make()
                ->title('Error saving profile')
                ->body('An error occurred while saving your profile. Please try again.')
                ->danger()
                ->send();
        }
    }
}
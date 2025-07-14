<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Application;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Actions\Action;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Filament\Resources\ApplicationResource\Pages\EditApplication;
use App\Filament\Resources\ApplicationResource\Pages\ViewApplication;
use App\Filament\Resources\ApplicationResource\Pages\ListApplications;
use App\Filament\Resources\ApplicationResource\Pages\CreateApplication;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    private const TYPE_OPTIONS = [
        'onsite' => 'Onsite',
        'remote' => 'Remote',
        'hybrid' => 'Hybrid',
        'freelance' => 'Freelance',
    ];

    private const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'interview' => 'Interview',
        'offer' => 'Offer',
        'rejected' => 'Rejected',
    ];

    private const NOTE_CATEGORIES = [
        'personal' => 'Personal',
        'professional' => 'Professional',
        'other' => 'Other',
    ];

    private const ACCEPTED_FILE_TYPES = ['application/pdf', 'application/msword'];
    private const MAX_FILE_SIZE = 10240;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Tabs')
                ->tabs([
                    self::buildApplicationTab(),
                    self::buildDocumentsTab(),
                    self::buildNotesTab(),
                    self::buildContactsTab(),
                    self::buildTasksTab(),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function buildApplicationTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Application')
            ->icon('heroicon-o-rectangle-stack')
            ->schema([
                self::buildBasicInfoGroup(),
                self::buildApplicationStatusGroup(),
                self::buildJobDescriptionField(),
                self::buildLocationTypeGroup(),
                self::buildJobDateGroup(),
                self::buildDocumentsGroup(),
            ]);
    }

    private static function buildBasicInfoGroup(): Group
    {
        return Group::make([
            Forms\Components\TextInput::make('user_id')
                ->default(auth()->user()->id)
                ->hidden()
                ->dehydratedWhenHidden(),
            Forms\Components\TextInput::make('job_title')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('company_name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('company_website')
                ->required()
                ->url()
                ->maxLength(255),
            Forms\Components\TextInput::make('application_link')
                ->prefixIcon('heroicon-o-link')
                ->required()
                ->url(),
        ])
            ->columns(['sm' => 1, 'md' => 2])
            ->columnSpanFull();
    }

    private static function buildDocumentsTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Documents')
            ->icon('heroicon-o-document-text')
            // ->badge(function ($record) {
            //     $resume = $record->resume?->count();
            //     $coverLetter = $record->coverLetter?->count();
            //     return $resume + $coverLetter;
            // })
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Resume Writer')
                            ->schema([
                                Section::make()
                                    ->description('in here you can generate your resume')
                                    ->schema([
                                        ViewField::make('resume_writer')
                                            ->view('filament.agents.resume-writer'),
                                    ]),
                            ]),
                        Tabs\Tab::make('Cover Letter Writer')
                            ->schema([
                                Section::make()
                                    ->description('in here you can generate your cover letter')
                                    ->schema([
                                        ViewField::make('cover_letter_writer')
                                            ->view('filament.agents.cover-letter-writer'),
                                    ]),
                            ]),
                    ])
            ]);
    }

    private static function buildApplicationStatusGroup(): Group
    {
        return Group::make([
            Forms\Components\DatePicker::make('applied_date')
                ->prefixIcon('heroicon-o-calendar')
                ->native(false)
                ->displayFormat('d M, Y')
                ->default(now())
                ->required(),
            Forms\Components\Select::make('status')
                ->options(self::STATUS_OPTIONS)
                ->default('pending')
                ->required(),
            Forms\Components\TextInput::make('salary_range')
                ->prefixIcon('heroicon-o-currency-dollar')
                ->required()
                ->maxLength(100),
        ])
            ->columns(['sm' => 1, 'md' => 2])
            ->columnSpanFull();
    }

    private static function buildJobDescriptionField(): Forms\Components\RichEditor
    {
        return Forms\Components\RichEditor::make('job_description')
            ->columnSpanFull()
            ->required();
    }

    private static function buildLocationTypeGroup(): Group
    {
        return Group::make([
            Forms\Components\TextInput::make('location')
                ->prefixIcon('heroicon-o-map-pin')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('job_type')
                ->options(self::TYPE_OPTIONS)
                ->default('remote')
                ->required(),
        ])
            ->columns(['sm' => 1, 'md' => 2])
            ->columnSpanFull();
    }

    private static function buildJobDateGroup(): Group
    {
        return Group::make([
            Forms\Components\DatePicker::make('posted_date')
                ->prefixIcon('heroicon-o-calendar')
                ->native(false)
                ->displayFormat('d M, Y'),
            Forms\Components\DatePicker::make('application_deadline')
                ->prefixIcon('heroicon-o-calendar')
                ->native(false)
                ->displayFormat('d M, Y')
                ->after('posted_date'),
        ])
            ->columns(['sm' => 1, 'md' => 2])
            ->columnSpanFull();
    }

    private static function buildDocumentsGroup(): Section
    {
        return Section::make('Documents')
            ->description('if you have costumized your resume or cover letter, you can upload them here or you can generate them in the documents tab')
            ->schema([
                Grid::make(['sm' => 1, 'md' => 2])
                    ->schema([
                        self::buildFileUpload('resume', 'resume', 'Resume'),
                        self::buildFileUpload('coverLetter', 'cover_letter', 'Cover Letter'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function buildFileUpload(string $relationship, string $column, string $label): Group
    {
        return Group::make([
            FileUpload::make($column)
                ->label($label)
                ->disk('public')
                ->directory(fn($get) => self::generateUploadDirectory($get('../company_name'), $column))
                ->acceptedFileTypes(self::ACCEPTED_FILE_TYPES)
                ->visibility('public')
                ->maxSize(self::MAX_FILE_SIZE)
                ->downloadable()
                ->openable()
                ->saveUploadedFileUsing(
                    fn($component, $file, Get $get) =>
                    self::saveUploadedFile($component, $file, $get, $column)
                )
                ->deletable()
                ->deleteUploadedFileUsing(function (string $file, $component) {
                    // Delete the file from storage
                    Storage::disk('public')->delete($file);

                    // Get the relationship record and delete it
                    $record = $component->getRecord();
                    if ($record) {
                        $record->delete(); // This deletes the entire relationship record
                    }
                })
        ])
            ->relationship(
                $relationship,
                condition: fn(?array $state): bool => filled($state[$column] ?? null)
            );
    }

    private static function generateUploadDirectory(string $companyName, string $column): string
    {
        $sanitizedCompanyName = self::sanitizeInput($companyName);
        return "applications/{$sanitizedCompanyName}/{$column}";
    }

    private static function saveUploadedFile(
        FileUpload $component,
        TemporaryUploadedFile $file,
        $get,
        string $column
    ): string {
        $userName = auth()->user()->name;
        $sanitizedUserName = self::sanitizeInput($userName);

        $companyName = $get('../company_name');
        $sanitizedCompanyName = self::sanitizeInput($companyName);

        $originalExtension = $file->getClientOriginalExtension();
        $typeLabel = $column === 'cover_letter' ? 'COVER_LETTER' : strtoupper($column);
        $filename = "{$sanitizedCompanyName}_{$sanitizedUserName}_{$typeLabel}.{$originalExtension}";

        return $file->storeAs($component->getDirectory(), $filename, $component->getDiskName());
    }

    private static function sanitizeInput(string $input): string
    {
        return Str::of($input)
            ->replace([',', '.', '&', '/', '\\', ':', '*', '?', '"', '<', '>', '|', '-', "'"], '')
            ->snake()
            ->upper();
    }


    private static function buildNotesTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Notes')
            ->icon('heroicon-o-document-text')
            ->badge(fn($record) => $record?->notes()->count())
            ->schema([
                Repeater::make('notes')
                    ->hiddenLabel()
                    ->relationship('notes')
                    ->grid(2)
                    ->schema([
                        Select::make('category')
                            ->options(self::NOTE_CATEGORIES),
                        RichEditor::make('content'),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(
                        fn(array $data) => self::filterEmptyNoteData($data)
                    )
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['category'] ?? 'New Note'
                    ),
            ]);
    }

    private static function buildContactsTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Contacts')
            ->icon('heroicon-o-user-group')
            ->badge(fn($record) => $record?->contacts()->count())
            ->schema([
                Repeater::make('contacts')
                    ->hiddenLabel()
                    ->relationship('contacts')
                    ->grid(2)
                    ->schema([
                        TextInput::make('name')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->prefixIcon('heroicon-o-envelope')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->prefixIcon('heroicon-o-phone')
                            ->maxLength(20),
                        TextInput::make('linkedin_profile')
                            ->prefixIcon('heroicon-o-link')
                            ->url()
                            ->maxLength(500)
                            ->hint(function ($state) {
                                if (filled($state)) {
                                    return new HtmlString(
                                        '<a href="' . $state . '" target="_blank" class="text-primary-600">View Profile</a>'
                                    );
                                }
                                return null;
                            }),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(
                        fn(array $data) => self::filterEmptyContactData($data)
                    )
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['name'] ?? 'New Contact'
                    ),
            ]);
    }

    private static function buildTasksTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Tasks')
            ->icon('heroicon-o-check-circle')
            ->badge(fn($record) => $record?->tasks()->count())
            ->schema([
                Repeater::make('tasks')
                    ->hiddenLabel()
                    ->grid(2)
                    ->relationship('tasks')
                    ->schema([
                        TextInput::make('title')
                            ->maxLength(255),
                        TextInput::make('description')
                            ->maxLength(500),
                        Checkbox::make('is_completed')
                            ->default(false),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(
                        fn(array $data) => self::filterEmptyTaskData($data)
                    )
                    ->itemLabel(
                        fn(array $state): ?string =>
                        $state['title'] ?? 'New Task'
                    ),
            ]);
    }

    // Data filtering methods
    private static function filterEmptyNoteData(array $data): ?array
    {
        $isCategoryEmpty = empty($data['category']);
        $isContentEmpty = empty($data['content']) ||
            Str::of(strip_tags($data['content']))->trim()->isEmpty();

        return ($isCategoryEmpty && $isContentEmpty) ? null : $data;
    }

    private static function filterEmptyContactData(array $data): ?array
    {
        $isEmpty = empty($data['name']) &&
            empty($data['email']) &&
            empty($data['phone']) &&
            empty($data['linkedin_profile']);

        return $isEmpty ? null : $data;
    }

    private static function filterEmptyTaskData(array $data): ?array
    {
        return (empty($data['title']) && empty($data['description'])) ? null : $data;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'view' => ViewApplication::route('/{record}'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}

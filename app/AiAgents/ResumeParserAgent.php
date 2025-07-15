<?php

namespace App\AiAgents;

use LarAgent\Agent;

class ResumeParserAgent extends Agent
{
    // protected $model = 'gemini-2.0-flash';
    protected $model = env('AI_MODEL');

    protected $history = 'in_memory';

    protected $provider = env('AI_PROVIDER');

    protected $temperature = 0.2;

    // this schema gives api key error //TODO fix it and use it 
    // protected $responseSchema = [
    //     'name' => 'ResumeData',
    //     'schema' => [
    //         'type' => 'object',
    //         'properties' => [
    //             'title' => [
    //                 'type' => ['string', 'null'],
    //                 'description' => 'Professional title or headline from the resume'
    //             ],
    //             'professional_summary' => [
    //                 'type' => ['string', 'null'],
    //                 'description' => 'Brief professional summary or objective statement'
    //             ],
    //             'skills' => [
    //                 'type' => 'array',
    //                 'items' => ['type' => 'string'],
    //                 'description' => 'List of technical and soft skills'
    //             ],
    //             'work_experiences' => [
    //                 'type' => 'array',
    //                 'items' => [
    //                     'type' => 'object',
    //                     'properties' => [
    //                         'company' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Company or organization name'
    //                         ],
    //                         'position' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Job title or position held'
    //                         ],
    //                         'start_date' => [
    //                             'type' => ['string', 'null'],
    //                             'format' => 'date',
    //                             'description' => 'Employment start date in YYYY-MM-DD format'
    //                         ],
    //                         'end_date' => [
    //                             'type' => ['string', 'null'],
    //                             'format' => 'date',
    //                             'description' => 'Employment end date in YYYY-MM-DD format. Use null for current positions'
    //                         ],
    //                         'achievements' => [
    //                             'type' => 'string',
    //                             'description' => 'Responsibilities and achievements formatted as markdown bullet points'
    //                         ],
    //                         'location' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Work location (city, state/country)'
    //                         ]
    //                     ],
    //                     'required' => ['company', 'position', 'start_date', 'achievements']
    //                 ],
    //                 'description' => 'Professional work experience in reverse chronological order'
    //             ],
    //             'education' => [
    //                 'type' => 'array',
    //                 'items' => [
    //                     'type' => 'object',
    //                     'properties' => [
    //                         'institution' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Educational institution name'
    //                         ],
    //                         'degree' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Degree type (e.g., Bachelor, Master, PhD)'
    //                         ],
    //                         'field_of_study' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Major, specialization, or field of study'
    //                         ],
    //                         'graduation_date' => [
    //                             'type' => ['string', 'null'],
    //                             'format' => 'date',
    //                             'description' => 'Graduation date in YYYY-MM-DD format'
    //                         ],
    //                         'gpa' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Grade Point Average if mentioned'
    //                         ],
    //                         'honors' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Academic honors, awards, or distinctions'
    //                         ],
    //                         'location' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Institution location'
    //                         ]
    //                     ],
    //                     'required' => ['institution', 'degree', 'graduation_date']
    //                 ],
    //                 'description' => 'Educational background in reverse chronological order'
    //             ],
    //             'certifications' => [
    //                 'type' => 'array',
    //                 'items' => [
    //                     'type' => 'object',
    //                     'properties' => [
    //                         'name' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Certification name'
    //                         ],
    //                         'issuer' => [
    //                             'type' => ['string', 'null'],
    //                             'description' => 'Certification issuing organization'
    //                         ],
    //                         'date_obtained' => [
    //                             'type' => ['string', 'null'],
    //                             'format' => 'date',
    //                             'description' => 'Date certification was obtained'
    //                         ],
    //                         'expiry_date' => [
    //                             'type' => ['string', 'null'],
    //                             'format' => 'date',
    //                             'description' => 'Certification expiry date if applicable'
    //                         ]
    //                     ],
    //                     'required' => ['name', 'issuer']
    //                 ],
    //                 'description' => 'Professional certifications and licenses'
    //             ],
    //             'contact_info' => [
    //                 'type' => 'object',
    //                 'properties' => [
    //                     'email' => [
    //                         'type' => ['string', 'null'],
    //                         'format' => 'email',
    //                         'description' => 'Email address'
    //                     ],
    //                     'phone' => [
    //                         'type' => ['string', 'null'],
    //                         'description' => 'Phone number'
    //                     ],
    //                     'linkedin' => [
    //                         'type' => ['string', 'null'],
    //                         'description' => 'LinkedIn profile URL'
    //                     ],
    //                     'website' => [
    //                         'type' => ['string', 'null'],
    //                         'description' => 'Personal website or portfolio URL'
    //                     ],
    //                     'location' => [
    //                         'type' => ['string', 'null'],
    //                         'description' => 'Current location (city, state/country)'
    //                     ]
    //                 ],
    //                 'description' => 'Contact information'
    //             ]
    //         ],
    //         'required' => [
    //             'title',
    //             'professional_summary',
    //             'skills',
    //             'work_experiences',
    //             'education'
    //         ]
    //     ]
    // ];

    protected $responseSchema = [
        'name' => 'ResumeData',
        'schema' => [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'professional_summary' => ['type' => 'string'],
                'skills' => [
                    'type' => 'array',
                    'items' => ['type' => 'string']
                ],
                'work_experiences' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'company' => ['type' => 'string'],
                            'position' => ['type' => 'string'],
                            'start_date' => [
                                'type' => ['string', 'null'],
                                'description' => 'Date in YYYY-MM-DD format.'
                            ],
                            'end_date' => [
                                'type' => ['string', 'null'],
                                'description' => 'Date in YYYY-MM-DD format. Null if current.'
                            ],
                            'achievements' => [
                                'type' => 'string',
                                'description' => 'A string containing bullet points detailing responsibilities and achievements.'
                            ]
                        ],
                        'required' => [
                            'company',
                            'position',
                            'start_date',
                            'achievements'
                        ]
                    ]
                ],
                'education' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'institution' => ['type' => 'string'],
                            'degree' => ['type' => 'string'],
                            'field_of_study' => [
                                'type' => 'string'
                            ],
                            'graduation_date' => [
                                'type' => ['string', 'null'],
                                'description' => 'Date in YYYY-MM-DD format.'
                            ],
                            'note' => ['type' => 'string']
                        ],
                        'required' => [
                            'institution',
                            'degree',
                            'graduation_date'
                        ]
                    ]
                ]
            ],
            'required' => [
                'title',
                'professional_summary',
                'skills',
                'work_experiences',
                'education'
            ]
        ]
    ];

    public function instructions(): string
    {
        return "You are an expert resume parsing agent designed to extract and structure information from resume text with high accuracy and consistency.

        CORE RESPONSIBILITIES:
        - Parse resume text and return structured data according to the provided JSON schema
        - Maintain data integrity and consistency across all fields
        - Handle various resume formats and layouts gracefully

        PARSING RULES:
        1. DATE FORMATTING:
        - All dates must be in YYYY-MM-DD format
        - If only year is provided (e.g., '2022'), use January 1st: '2022-01-01'
        - If month-year is provided (e.g., 'June 2022'), use the first day: '2022-06-01'
        - For current positions, use null for end_date
        - Be consistent with date assumptions across the entire resume

        2. ACHIEVEMENTS FORMATTING:
        - Combine all bullet points into a single string for work experiences
        - Preserve bullet point formatting using markdown (- or *)
        - Maintain original structure and hierarchy
        - Include quantifiable achievements and metrics when available

        3. DATA HANDLING:
        - Use null for missing or unavailable fields
        - Normalize company names and job titles (proper capitalization)
        - Extract contact information carefully, validating email formats
        - Categorize skills appropriately (technical, soft, language, etc.)

        4. QUALITY ASSURANCE:
        - Ensure chronological order (most recent first)
        - Validate required fields are populated
        - Cross-reference information for consistency
        - Handle edge cases gracefully (career gaps, multiple degrees, etc.)

        5. ADDITIONAL CONTEXT:
        - Extract location information when available
        - Include GPA only if explicitly mentioned
        - Capture certifications and professional licenses
        - Preserve important details like honors and distinctions";
    }

    public function prompt(string $message): string
    {
        return "Please parse the following resume text with attention to detail and accuracy. Extract all relevant information according to the schema and formatting rules:\n\n" . $message;
    }
}

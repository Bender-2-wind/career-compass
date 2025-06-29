<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->randomElement([
                'Senior Software Engineer',
                'Full Stack Developer',
                'Product Manager',
                'UX/UI Designer',
                'Data Scientist',
                'Marketing Specialist',
                'Project Manager',
            ]),
            'professional_summary' => $this->faker->paragraph(3),
            'skills' => $this->faker->randomElements([ // Randomly select 5-10 skills
                'Laravel',
                'PHP',
                'Vue.js',
                'React',
                'JavaScript',
                'Tailwind CSS',
                'Bootstrap',
                'MySQL',
                'PostgreSQL',
                'MongoDB',
                'Redis',
                'AWS',
                'Docker',
                'Kubernetes',
                'Git',
                'CI/CD',
                'Agile Methodologies',
                'Scrum',
                'API Development',
                'Unit Testing',
                'System Design',
                'Cloud Computing',
                'Machine Learning',
                'Data Analysis',
                'UI/UX Design',
                'Figma',
                'Adobe XD',
                'Content Marketing',
                'SEO',
                'SEM',
                'Project Management',
                'SCRUM Master',
                'Team Leadership',
                'Communication',
            ], $this->faker->numberBetween(5, 10)),
            'work_experiences' => $this->generateWorkExperiences(),
            'education' => $this->generateEducation(),
        ];
    }

    /**
     * Generate fake work experiences.
     */
    private function generateWorkExperiences(): array
    {
        $experiences = [];
        $numExperiences = $this->faker->numberBetween(1, 3); // 1 to 3 work experiences

        for ($i = 0; $i < $numExperiences; $i++) {
            $startDate = $this->faker->dateTimeBetween('-10 years', '-2 years');
            $endDate = ($i === 0) ? null : $this->faker->dateTimeBetween($startDate, 'now'); // Last job might be current

            $experiences[] = [
                'company' => $this->faker->company(),
                'position' => $this->faker->randomElement([
                    'Software Engineer',
                    'Senior Developer',
                    'Lead Engineer',
                    'Product Analyst',
                    'Marketing Manager',
                    'Creative Designer',
                    'Data Analyst',
                    'Project Coordinator',
                ]),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                'description' => $this->faker->paragraph(2),
            ];
        }
        return $experiences;
    }

    /**
     * Generate fake education entries.
     */
    private function generateEducation(): array
    {
        $education = [];
        $numEducation = $this->faker->numberBetween(1, 2); // 1 to 2 education entries

        for ($i = 0; $i < $numEducation; $i++) {
            $graduationDate = $this->faker->dateTimeBetween('-8 years', 'now');

            $education[] = [
                'institution' => $this->faker->randomElement([
                    'University of Technology',
                    'State University of ' . $this->faker->city(),
                    'Polytechnic Institute',
                    'Community College of ' . $this->faker->word(),
                ]),
                'degree' => $this->faker->randomElement([
                    'Bachelor of Science',
                    'Master of Science',
                    'Associate of Arts',
                    'PhD',
                ]),
                'field_of_study' => $this->faker->randomElement([
                    'Computer Science',
                    'Software Engineering',
                    'Business Administration',
                    'Marketing',
                    'Graphic Design',
                    'Data Science',
                    'Electrical Engineering',
                ]),
                'graduation_date' => $graduationDate->format('Y-m-d'),
            ];
        }
        return $education;
    }
}

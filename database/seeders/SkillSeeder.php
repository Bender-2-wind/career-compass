<?php

namespace Database\Seeders;

use Spatie\Tags\Tag;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            'JavaScript' => 'JavaScript',
            'Python' => 'Python',
            'Java' => 'Java',
            'C++' => 'C++',
            'C#' => 'C#',
            'PHP' => 'PHP',
            'TypeScript' => 'TypeScript',
            'HTML' => 'HTML',
            'CSS' => 'CSS',
            'SQL' => 'SQL',
            'React' => 'React',
            'Angular' => 'Angular',
            'Vue' => 'Vue',
            'Node' => 'Node',
            'Express' => 'Express',
            'Laravel' => 'Laravel',
            'Django' => 'Django',
            'Flask' => 'Flask',
            'Teamwork' => 'Teamwork',
            'Leadership' => 'Leadership',
            'Communication' => 'Communication',
            'Time management' => 'Time management',
            'Problem-solving' => 'Problem-solving',
            'Customer service' => 'Customer service',
            'Team player' => 'Team player',
            'Organized' => 'Organized',
            'Analytical' => 'Analytical',
            'Creative' => 'Creative',
            'Attention to detail' => 'Attention to detail',
            'Data structures' => 'Data structures',
            'Algorithms' => 'Algorithms',
            'OOD' => 'OOD',
            'Agile' => 'Agile',
            'Scrum' => 'Scrum',
            'Kanban' => 'Kanban',
            'Testing' => 'Testing',
            'Debugging' => 'Debugging',
            'Refactoring' => 'Refactoring',
            'API design' => 'API design',
            'Cloud computing' => 'Cloud computing',
            'Docker' => 'Docker',
            'Kubernetes' => 'Kubernetes',
        ];

        foreach ($skills as $skill) {  
            Tag::findOrCreate($skill, 'skills');  
        }  
    }
}

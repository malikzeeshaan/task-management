<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $adminId = $userIds[0]; // Admin User is always first

        $tasks = [
            // Pending — due in the future
            [
                'title'            => 'Inspect fire extinguishers',
                'description'      => 'Check all fire extinguishers on site for expiry and pressure.',
                'due_date'         => now()->addDays(5),
                'assigned_user_id' => $userIds[0],
                'priority'         => TaskPriority::High,
                'status'           => TaskStatus::Pending,
            ],
            [
                'title'            => 'Update safety data sheets',
                'description'      => 'Ensure all chemical SDS documents are current.',
                'due_date'         => now()->addDays(10),
                'assigned_user_id' => $userIds[1],
                'priority'         => TaskPriority::Medium,
                'status'           => TaskStatus::Pending,
            ],
            [
                'title'            => 'Review emergency evacuation plan',
                'description'      => 'Walk through the evacuation plan with the site team.',
                'due_date'         => now()->addDays(14),
                'assigned_user_id' => $userIds[2],
                'priority'         => TaskPriority::Medium,
                'status'           => TaskStatus::Pending,
            ],
            [
                'title'            => 'Order replacement PPE stock',
                'description'      => 'Reorder hard hats, gloves, and hi-vis vests.',
                'due_date'         => now()->addDays(7),
                'assigned_user_id' => $userIds[3],
                'priority'         => TaskPriority::Low,
                'status'           => TaskStatus::Pending,
            ],

            // Due today — pending
            [
                'title'            => 'Conduct daily site safety briefing',
                'description'      => 'Brief all workers on today\'s hazards before shift start.',
                'due_date'         => today(),
                'assigned_user_id' => $userIds[0],
                'priority'         => TaskPriority::High,
                'status'           => TaskStatus::Pending,
            ],
            [
                'title'            => 'Test emergency alarm system',
                'description'      => 'Run a scheduled test of the alarm system.',
                'due_date'         => today(),
                'assigned_user_id' => $userIds[1],
                'priority'         => TaskPriority::Medium,
                'status'           => TaskStatus::Pending,
            ],

            // Overdue — pending with past due dates
            [
                'title'            => 'Submit monthly compliance report',
                'description'      => 'Compile and submit the compliance report for last month.',
                'due_date'         => now()->subDays(3),
                'assigned_user_id' => $userIds[2],
                'priority'         => TaskPriority::High,
                'status'           => TaskStatus::Pending,
            ],
            [
                'title'            => 'Inspect scaffolding on Block B',
                'description'      => 'Check structural integrity of all scaffolding on Block B.',
                'due_date'         => now()->subDays(7),
                'assigned_user_id' => $userIds[3],
                'priority'         => TaskPriority::High,
                'status'           => TaskStatus::Pending,
            ],
            [
                'title'            => 'Review contractor induction records',
                'description'      => 'Confirm all contractors have completed site induction.',
                'due_date'         => now()->subDays(1),
                'assigned_user_id' => $userIds[0],
                'priority'         => TaskPriority::Medium,
                'status'           => TaskStatus::Pending,
            ],

            // Completed
            [
                'title'            => 'Install safety signage in warehouse',
                'description'      => 'Place required safety signs at all entry and hazard points.',
                'due_date'         => now()->subDays(10),
                'assigned_user_id' => $userIds[1],
                'priority'         => TaskPriority::Medium,
                'status'           => TaskStatus::Completed,
            ],
            [
                'title'            => 'Conduct first aid kit audit',
                'description'      => 'Check all first aid kits are stocked and within expiry.',
                'due_date'         => now()->subDays(5),
                'assigned_user_id' => $userIds[2],
                'priority'         => TaskPriority::Low,
                'status'           => TaskStatus::Completed,
            ],
            [
                'title'            => 'Register new site vehicles',
                'description'      => 'Ensure all new vehicles are registered in the site log.',
                'due_date'         => now()->subDays(2),
                'assigned_user_id' => $userIds[3],
                'priority'         => TaskPriority::Low,
                'status'           => TaskStatus::Completed,
            ],

            // Non-compliant with corrective actions
            [
                'title'            => 'Repair damaged floor markings in Zone C',
                'description'      => 'Floor markings have faded and need repainting.',
                'due_date'         => now()->subDays(6),
                'assigned_user_id' => $userIds[0],
                'priority'         => TaskPriority::Medium,
                'status'           => TaskStatus::NonCompliant,
                'corrective_action' => 'Markings were not repainted due to supplier delay. New materials ordered and work scheduled for next Monday.',
            ],
            [
                'title'            => 'Replace faulty electrical panel cover',
                'description'      => 'Panel cover in server room is broken and poses a hazard.',
                'due_date'         => now()->subDays(4),
                'assigned_user_id' => $userIds[1],
                'priority'         => TaskPriority::High,
                'status'           => TaskStatus::NonCompliant,
                'corrective_action' => 'Cover replacement part is on back-order. Temporary barrier installed and area restricted until part arrives.',
            ],
            [
                'title'            => 'Complete noise exposure assessment',
                'description'      => 'Carry out noise level measurements across all workshop areas.',
                'due_date'         => now()->subDays(8),
                'assigned_user_id' => $userIds[2],
                'priority'         => TaskPriority::Medium,
                'status'           => TaskStatus::NonCompliant,
                'corrective_action' => 'Assessment could not be completed as the contracted assessor cancelled. Rescheduled for end of the week.',
            ],
        ];

        foreach ($tasks as $task) {
            Task::create(array_merge($task, ['created_by' => $adminId]));
        }
    }
}

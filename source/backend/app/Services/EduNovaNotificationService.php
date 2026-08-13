<?php
namespace App\Services;

use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\UserNotification;
use Illuminate\Support\Collection;

class EduNovaNotificationService
{
    public function classroomStudentIds(Classroom $classroom): Collection
    {
        $classroom->loadMissing('teachingAssignment');
        $classGroupId = $classroom->teachingAssignment?->class_group_id;
        if (!$classGroupId) return collect();

        return ClassEnrollment::query()
            ->where('class_group_id', $classGroupId)
            ->where('status', 'active')
            ->pluck('student_id');
    }

    public function notifyUsers(iterable $userIds, string $type, string $title, ?string $body = null, ?string $actionUrl = null, array $data = []): void
    {
        foreach ($userIds as $userId) {
            UserNotification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'action_url' => $actionUrl,
                'data' => $data ?: null,
            ]);
        }
    }

    public function notifyClassroomStudents(Classroom $classroom, string $type, string $title, ?string $body = null, ?string $actionUrl = null, array $data = []): void
    {
        $this->notifyUsers($this->classroomStudentIds($classroom), $type, $title, $body, $actionUrl, $data);
    }
}

<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $unreadOnly = $request->boolean('unread');
        $query = UserNotification::where('user_id', $request->user()->id)->latest();
        if ($unreadOnly) $query->whereNull('read_at');
        $items = $query->limit(50)->get();
        return response()->json([
            'data' => $items,
            'meta' => ['unread_count' => UserNotification::where('user_id',$request->user()->id)->whereNull('read_at')->count()],
        ]);
    }

    public function read(Request $request, int $id): JsonResponse
    {
        $item = UserNotification::where('user_id',$request->user()->id)->findOrFail($id);
        $item->update(['read_at'=>now()]);
        return response()->json(['data'=>$item]);
    }

    public function readAll(Request $request): JsonResponse
    {
        UserNotification::where('user_id',$request->user()->id)->whereNull('read_at')->update(['read_at'=>now()]);
        return response()->json(['message'=>'Semua notifikasi ditandai sudah dibaca.']);
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/bookings",
     *     summary="Get all bookings",
     *     tags={"AdminBookings"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all bookings",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Booking")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Booking::latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate();

        return response()->json($bookings, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/bookings/{id}",
     *     summary="Get booking details",
     *     tags={"AdminBookings"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Booking ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking details",
     *         @OA\JsonContent(ref="#/components/schemas/Booking")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function show($id)
    {
        $booking = Booking::with('user', 'roomType')->findOrFail($id);

        return response()->json($booking, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/bookings/{id}",
     *     summary="Update booking status",
     *     tags={"AdminBookings"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Booking ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "confirmed", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Booking")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(BookingStatus::class)],
        ]);

        $booking->update($validated);

        return response()->json($booking, 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmptySuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\CreateFeedback;
use App\Pipes\CreateTicket;
use App\Pipes\GetTickets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class TicketAndFeedbackController extends Controller
{
    /**
     * @authenticated
     *
     * @group Ticket And Feedback Actions
     */
    public function getTickets(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(...Pipeline::send($request)
            ->through([
                GetTickets::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     *
     * @queryParam question string The question. Example: what is this app?
     *
     * @group Ticket And Feedback Actions
     */
    public function createTicket(Request $request): EmptySuccessfulResponseResource
    {
        Pipeline::send($request)
            ->through([
                CreateTicket::class,
            ])
            ->thenReturn();

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @authenticated
     *
     * @queryParam feedback string The feedback. Example: Make more offers
     *
     * @group Ticket And Feedback Actions
     */
    public function createFeedback(Request $request): EmptySuccessfulResponseResource
    {
        Pipeline::send($request)
            ->through([
                CreateFeedback::class,
            ])
            ->thenReturn();

        return new EmptySuccessfulResponseResource;
    }
}

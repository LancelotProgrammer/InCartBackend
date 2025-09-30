<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmptySuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\AuthorizeUser;
use App\Pipes\CreateFeedback;
use App\Pipes\CreateTicket;
use App\Pipes\GetTickets;
use App\Pipes\ValidateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class TicketAndFeedbackController extends Controller
{
    /**
     * @authenticated
     *
     * @group Product Ticket And Feedback
     */
    public function getTickets(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
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
     * @group Product Ticket And Feedback
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
     * @group Product Ticket And Feedback
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

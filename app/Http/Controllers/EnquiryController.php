<?php

namespace App\Http\Controllers;
use App\Helpers\PhonePeHelper;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function submitEnquiry(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'country' => 'required|string',
            'contact_number' => 'required|string',
            'numChildren' => 'required|integer',
            'childAge' => 'required|array',
            'childAge.*' => 'required|integer',
            'subject' => 'required|string',
            'message' => 'nullable|string',
        ]);

        $data = $request->all();

        // Generate unique transaction and order IDs
        $transactionId = uniqid('txn_');
        $merchantTransactionId = 'TXN' . time(); // TXN followed by the current timestamp

        // Prepare payload for PhonePe payment request
        $payload = [
            'merchantId' => env('MERCENT_ID'),
            'merchantTransactionId' => $merchantTransactionId,
            'amount' => 1000 * 100, // Amount in paisa (₹10.00)
            'message' => 'Payment for ' . $data['subject'],
            'redirectUrl' => route('payment.callback'),
            'callbackUrl' => route('payment.callback'),
            'paymentInstrument' => [
                'type' => 'PAY_PAGE', // Using PhonePe Pay Page
            ],
        ];

        try {
            // Initialize PhonePe Helper and initiate payment
            $phonePeHelper = new PhonePeHelper();
            $response = $phonePeHelper->initiatePayment($payload);
            
            if (isset($response['success']) && $response['success'] === true) {

                return response()->json([
                    'message' => 'Enquiry submitted and payment initiated!',
                    'url' => $response['data']['instrumentResponse']['redirectInfo']['url'],
                    'data' => $data,
                ],200);
            }

            return response()->json([
                'message' => 'Enquiry submitted, but payment initiation failed.',
                'error' => $response['message'] ?? 'Unknown error',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during payment initiation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function handleCallback(Request $request)
    {
        $data = $request->all();

        // Log or handle the payment response
        return response()->json([
            'message' => 'Payment callback received.',
            'data' => $data,
        ]);
    }
}

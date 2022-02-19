<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TransferController extends Controller
{
    public function deposit(Request $request) {
        try {
            request()->validate([
                'amount' => 'required',
                // and others validation rules
            ]);
        } catch (\Exception $e){
            return response(['error' => $e->getMessage()], 400);

        }
        $clientId = 'clientId';
        $trackId = 'transfer-to-deposit-0323';
        $token = 'token';
        $fields = $request->all();

        // if need to and others like secondPassword set them here
        $fields['user_id'] = Auth::user()->getAuthIdentifier();
        $fields['secondPassword'] = '34345345';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$token}"
        ])->post("https://apibeta.finnotech.ir/oak/v2/clients/{$clientId}/transferTo?trackId={$trackId}", $fields);

        try {
            $json = $response->json();
            $transfer = new Transfer();
            $fields['transaction_result'] = $json;
            if ($transfer->fill($fields)->save()) {
                return response(['success' => true]);
            }
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], 400);
        }
    }

    public function info(Request $request) {
        try {
            request()->validate([
                'deposit' => 'required',
                // and others validation rules
            ]);
        } catch (\Exception $e){
            return response(['error' => $e->getMessage()], 400);

        }
        $fields = $request->all();
        try {
            // this is simple example, but can be n-to-no relation
            $profile = new Profile();
            $fields['user_id'] = Auth::user()->getAuthIdentifier();
            if ($profile->fill($fields)->save()) {
                return response(['success' => true]);
            }
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], 400);
        }
    }

    public function list() {
        return Transfer::where('user_id', Auth::user()->getAuthIdentifier())->paginate()->toArray();
    }

}

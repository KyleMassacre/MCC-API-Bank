<?php

namespace Modules\Bank\Http\Requests;

use Dingo\Api\Http\FormRequest;

class BankRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'required|integer|min:2'
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'You must provide a transaction amount',
            'amount.integer' => 'You must provide a valid transaction amount',
            'amount.min'    => 'Your transaction amount must be at least '.env('CURRENCY_SYMBOL','$').':min'
        ];
    }
}

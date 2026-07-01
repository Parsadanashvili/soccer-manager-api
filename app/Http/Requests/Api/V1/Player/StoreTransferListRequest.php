<?php

namespace App\Http\Requests\Api\V1\Player;

use App\Models\Player;
use App\Models\TransferList;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransferListRequest extends FormRequest
{
    public function authorize(): bool
    {
        $player = $this->route('player');

        return $player instanceof Player
            && $this->user()->can('create', [TransferList::class, $player]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'asking_price' => ['required', 'integer', 'min:1'],
        ];
    }


    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $player = $this->route('player');

            if ($player instanceof Player && $player->transferListing()->exists()) {
                $validator->errors()->add('player', __('transfer_list.already_listed'));
            }
        });
    }
}

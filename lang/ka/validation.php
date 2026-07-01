<?php

/*
 * Georgian validation messages. Only the rules used by this API are
 * translated here; any missing key falls back to the English file
 * (fallback_locale = 'en').
 */

return [
    'required' => ':attribute ველის შევსება სავალდებულოა.',
    'string' => ':attribute უნდა იყოს ტექსტი.',
    'email' => ':attribute უნდა იყოს სწორი ელფოსტის მისამართი.',
    'integer' => ':attribute უნდა იყოს მთელი რიცხვი.',
    'unique' => 'ასეთი :attribute უკვე არსებობს.',
    'confirmed' => ':attribute-ის დადასტურება არ ემთხვევა.',

    'min' => [
        'string' => ':attribute უნდა შეიცავდეს მინიმუმ :min სიმბოლოს.',
        'numeric' => ':attribute არ უნდა იყოს :min-ზე ნაკლები.',
    ],

    'max' => [
        'string' => ':attribute არ უნდა აღემატებოდეს :max სიმბოლოს.',
        'numeric' => ':attribute არ უნდა აღემატებოდეს :max-ს.',
    ],

    /*
     * Human-readable attribute names used in the messages above.
     */
    'attributes' => [
        'name' => 'სახელი',
        'email' => 'ელფოსტა',
        'password' => 'პაროლი',
        'country' => 'ქვეყანა',
        'first_name' => 'სახელი',
        'last_name' => 'გვარი',
        'asking_price' => 'ფასი',
        'player' => 'მოთამაშე',
    ],
];

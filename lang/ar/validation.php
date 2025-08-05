<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'يجب قبول :attribute.',
    'accepted_if' => 'يجب قبول :attribute عندما يكون :other هو :value.',
    'active_url' => ':attribute يجب أن يكون رابطاً صحيحاً.',
    'after' => ':attribute يجب أن يكون تاريخاً بعد :date.',
    'after_or_equal' => ':attribute يجب أن يكون تاريخاً بعد أو يساوي :date.',
    'alpha' => ':attribute يجب أن يحتوي على أحرف فقط.',
    'alpha_dash' => ':attribute يجب أن يحتوي على أحرف، أرقام، شرطات وشرطات سفلية فقط.',
    'alpha_num' => ':attribute يجب أن يحتوي على أحرف وأرقام فقط.',
    'any_of' => ':attribute غير صالح.',
    'array' => ':attribute يجب أن يكون مصفوفة.',
    'ascii' => ':attribute يجب أن يحتوي فقط على رموز وأحرف أحادية البايت.',
    'before' => ':attribute يجب أن يكون تاريخاً قبل :date.',
    'before_or_equal' => ':attribute يجب أن يكون تاريخاً قبل أو يساوي :date.',
    'between' => [
        'array' => ':attribute يجب أن يحتوي بين :min و :max عنصراً.',
        'file' => ':attribute يجب أن يكون بين :min و :max كيلوبايت.',
        'numeric' => ':attribute يجب أن يكون بين :min و :max.',
        'string' => ':attribute يجب أن يكون بين :min و :max حرفاً.',
    ],
    'boolean' => ':attribute يجب أن يكون صحيحاً أو خطأ.',
    'can' => ':attribute يحتوي على قيمة غير مصرح بها.',
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'contains' => ':attribute يفتقد إلى قيمة مطلوبة.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => ':attribute يجب أن يكون تاريخاً صحيحاً.',
    'date_equals' => ':attribute يجب أن يكون تاريخاً يساوي :date.',
    'date_format' => ':attribute لا يطابق التنسيق :format.',
    'decimal' => ':attribute يجب أن يحتوي على :decimal منازل عشرية.',
    'declined' => 'يجب رفض :attribute.',
    'declined_if' => 'يجب رفض :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن يكون :attribute و :other مختلفين.',
    'digits' => ':attribute يجب أن يحتوي على :digits رقم.',
    'digits_between' => ':attribute يجب أن يكون بين :min و :max رقماً.',
    'dimensions' => ':attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct' => ':attribute يحتوي على قيمة مكررة.',
    'doesnt_end_with' => ':attribute لا يجب أن ينتهي بأحد القيم التالية: :values.',
    'doesnt_start_with' => ':attribute لا يجب أن يبدأ بأحد القيم التالية: :values.',
    'email' => ':attribute يجب أن يكون بريد إلكتروني صالح.',
    'ends_with' => ':attribute يجب أن ينتهي بأحد القيم التالية: :values.',
    'enum' => 'القيمة المحددة في :attribute غير صالحة.',
    'exists' => 'القيمة المحددة في :attribute غير صالحة.',
    'extensions' => ':attribute يجب أن يحتوي على أحد الامتدادات التالية: :values.',
    'file' => ':attribute يجب أن يكون ملفاً.',
    'filled' => ':attribute يجب أن يحتوي على قيمة.',
    'gt' => [
        'array' => ':attribute يجب أن يحتوي على أكثر من :value عنصر.',
        'file' => ':attribute يجب أن يكون أكبر من :value كيلوبايت.',
        'numeric' => ':attribute يجب أن يكون أكبر من :value.',
        'string' => ':attribute يجب أن يكون أكبر من :value حرفاً.',
    ],
    'gte' => [
        'array' => ':attribute يجب أن يحتوي على :value عنصر أو أكثر.',
        'file' => ':attribute يجب أن يكون أكبر من أو يساوي :value كيلوبايت.',
        'numeric' => ':attribute يجب أن يكون أكبر من أو يساوي :value.',
        'string' => ':attribute يجب أن يكون أكبر من أو يساوي :value حرفاً.',
    ],
    'hex_color' => ':attribute يجب أن يكون لونًا عشريًا سداسيًا صالحًا.',
    'image' => ':attribute يجب أن يكون صورة.',
    'in' => ':attribute المحدد غير صالح.',
    'in_array' => ':attribute يجب أن يكون موجودًا في :other.',
    'in_array_keys' => ':attribute يجب أن يحتوي على مفتاح واحد على الأقل من القيم التالية: :values.',
    'integer' => ':attribute يجب أن يكون عددًا صحيحًا.',
    'ip' => ':attribute يجب أن يكون عنوان IP صالح.',
    'ipv4' => ':attribute يجب أن يكون عنوان IPv4 صالح.',
    'ipv6' => ':attribute يجب أن يكون عنوان IPv6 صالح.',
    'json' => ':attribute يجب أن يكون سلسلة JSON صالحة.',
    'list' => ':attribute يجب أن يكون قائمة.',
    'lowercase' => ':attribute يجب أن يكون بأحرف صغيرة.',
    'lt' => [
        'array' => ':attribute يجب أن يحتوي على أقل من :value عنصر.',
        'file' => ':attribute يجب أن يكون أقل من :value كيلوبايت.',
        'numeric' => ':attribute يجب أن يكون أقل من :value.',
        'string' => ':attribute يجب أن يكون أقل من :value حرفاً.',
    ],
    'lte' => [
        'array' => ':attribute يجب ألا يحتوي على أكثر من :value عنصر.',
        'file' => ':attribute يجب أن يكون أقل من أو يساوي :value كيلوبايت.',
        'numeric' => ':attribute يجب أن يكون أقل من أو يساوي :value.',
        'string' => ':attribute يجب أن يكون أقل من أو يساوي :value حرفاً.',
    ],
    'mac_address' => ':attribute يجب أن يكون عنوان MAC صالح.',
    'max' => [
        'array' => ':attribute يجب ألا يحتوي على أكثر من :max عنصر.',
        'file' => ':attribute لا يجب أن يتجاوز :max كيلوبايت.',
        'numeric' => ':attribute لا يجب أن يتجاوز :max.',
        'string' => ':attribute لا يجب أن يتجاوز :max حرفاً.',
    ],
    'max_digits' => ':attribute لا يجب أن يحتوي على أكثر من :max رقم.',
    'mimes' => ':attribute يجب أن يكون ملفاً من النوع: :values.',
    'mimetypes' => ':attribute يجب أن يكون ملفاً من النوع: :values.',
    'min' => [
        'array' => ':attribute يجب أن يحتوي على الأقل على :min عنصر.',
        'file' => ':attribute يجب أن يكون على الأقل :min كيلوبايت.',
        'numeric' => ':attribute يجب أن يكون على الأقل :min.',
        'string' => ':attribute يجب أن يكون على الأقل :min حرفاً.',
    ],
    'min_digits' => ':attribute يجب أن يحتوي على الأقل على :min رقم.',
    'missing' => 'يجب أن يكون :attribute غير موجود.',
    'missing_if' => 'يجب أن يكون :attribute غير موجود عندما يكون :other هو :value.',
    'missing_unless' => 'يجب أن يكون :attribute غير موجود إلا إذا كان :other هو :value.',
    'missing_with' => 'يجب أن يكون :attribute غير موجود عندما يكون :values موجوداً.',
    'missing_with_all' => 'يجب أن يكون :attribute غير موجود عندما تكون :values موجودة.',
    'multiple_of' => ':attribute يجب أن يكون من مضاعفات :value.',
    'not_in' => 'القيمة المحددة في :attribute غير صالحة.',
    'not_regex' => 'تنسيق :attribute غير صالح.',
    'numeric' => ':attribute يجب أن يكون رقماً.',
    'password' => [
        'letters' => ':attribute يجب أن يحتوي على حرف واحد على الأقل.',
        'mixed' => ':attribute يجب أن يحتوي على حرف كبير وصغير على الأقل.',
        'numbers' => ':attribute يجب أن يحتوي على رقم واحد على الأقل.',
        'symbols' => ':attribute يجب أن يحتوي على رمز واحد على الأقل.',
        'uncompromised' => ':attribute ظهر في تسريب بيانات. يرجى اختيار كلمة مرور مختلفة.',
    ],
    'present' => ':attribute يجب أن يكون موجوداً.',
    'present_if' => ':attribute يجب أن يكون موجوداً عندما يكون :other هو :value.',
    'present_unless' => ':attribute يجب أن يكون موجوداً إلا إذا كان :other هو :value.',
    'present_with' => ':attribute يجب أن يكون موجوداً عندما يكون :values موجوداً.',
    'present_with_all' => ':attribute يجب أن يكون موجوداً عندما تكون :values موجودة.',
    'prohibited' => ':attribute محظور.',
    'prohibited_if' => ':attribute محظور عندما يكون :other هو :value.',
    'prohibited_if_accepted' => ':attribute محظور عندما يتم قبول :other.',
    'prohibited_if_declined' => ':attribute محظور عندما يتم رفض :other.',
    'prohibited_unless' => ':attribute محظور ما لم يكن :other في :values.',
    'prohibits' => ':attribute يمنع :other من التواجد.',
    'regex' => 'تنسيق :attribute غير صالح.',
    'required' => ':attribute مطلوب.',
    'required_array_keys' => ':attribute يجب أن يحتوي على مفاتيح: :values.',
    'required_if' => ':attribute مطلوب عندما يكون :other هو :value.',
    'required_if_accepted' => ':attribute مطلوب عندما يتم قبول :other.',
    'required_if_declined' => ':attribute مطلوب عندما يتم رفض :other.',
    'required_unless' => ':attribute مطلوب ما لم يكن :other في :values.',
    'required_with' => ':attribute مطلوب عندما يكون :values موجوداً.',
    'required_with_all' => ':attribute مطلوب عندما تكون :values موجودة.',
    'required_without' => ':attribute مطلوب عندما لا يكون :values موجوداً.',
    'required_without_all' => ':attribute مطلوب عندما لا تكون أي من :values موجودة.',
    'same' => ':attribute يجب أن يطابق :other.',
    'size' => [
        'array' => ':attribute يجب أن يحتوي على :size عنصر.',
        'file' => ':attribute يجب أن يكون :size كيلوبايت.',
        'numeric' => ':attribute يجب أن يكون :size.',
        'string' => ':attribute يجب أن يكون :size حرفاً.',
    ],
    'starts_with' => ':attribute يجب أن يبدأ بأحد القيم التالية: :values.',
    'string' => ':attribute يجب أن يكون سلسلة نصية.',
    'timezone' => ':attribute يجب أن يكون منطقة زمنية صالحة.',
    'unique' => ':attribute مستخدم بالفعل.',
    'uploaded' => 'فشل تحميل :attribute.',
    'uppercase' => ':attribute يجب أن يكون بأحرف كبيرة.',
    'url' => ':attribute يجب أن يكون رابطًا صالحًا.',
    'ulid' => ':attribute يجب أن يكون ULID صالح.',
    'uuid' => ':attribute يجب أن يكون UUID صالح.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'رسالة مخصصة',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];

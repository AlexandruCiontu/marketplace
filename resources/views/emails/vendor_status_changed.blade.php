@component('mail::message')
# Account Status Update

Hello {{ $vendor->user->name }},

Your vendor account status has been updated to: **{{ ucfirst($vendor->status) }}**.

@component('mail::button', ['url' => url('/vendor/dashboard')])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

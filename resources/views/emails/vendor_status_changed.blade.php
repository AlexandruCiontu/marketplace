@component('mail::message')
    # Vendor Account Status Updated

    Hello {{ $vendor->name }},

    We wanted to inform you that the status of your vendor account has changed.

    **New Status:** {{ ucfirst($vendor->status) }}

    If you have any questions, feel free to contact our support team.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent



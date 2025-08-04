@component('mail::message')
# New Vendor Request

A new vendor has requested approval.

**Name:** {{ $vendor->user?->name ?? 'N/A' }}
**Email:** {{ $vendor->user?->email ?? 'N/A' }}

@component('mail::button', ['url' => url('/admin/vendors')])
View Vendor
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

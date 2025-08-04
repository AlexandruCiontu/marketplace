<?php

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Http\Resources\ProductListResource;
use App\Mail\NewVendorRequest;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function profile(Request $request, Vendor $vendor)
    {
        $keyword = $request->query('keyword');
        $products = Product::query()
            ->forWebsite()
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('description', 'LIKE', "%{$keyword}%");
                });
            })
            ->where('created_by', $vendor->user_id)
            ->paginate(24);

        return Inertia::render('Vendor/Profile', [
            'vendor' => $vendor,
            'products' => ProductListResource::collection($products),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        // Now this works
        $request->validate([
            'store_name' => [
                'required',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('vendors', 'store_name')
                    ->ignore($user->id, 'user_id')
            ],
            'store_address' => 'nullable',
            'country_code' => 'required|string|in:RO,HU,BG',
            'phone' => 'required|string|max:20',
            'anaf_pfx' => 'nullable|required_if:country_code,RO|file|mimetypes:application/x-pkcs12|mimes:pfx|max:2048',
            'nav_user_id' => 'nullable|required_if:country_code,HU|string|max:255',
            'nav_exchange_key' => 'nullable|required_if:country_code,HU|string|max:255',
        ], [
            'store_name.regex' => 'Store Name must only contain lowercase alphanumeric characters and dashes.',
        ]);
        $isNewVendor = !$user->vendor;

        $vendor = $user->vendor ?: new Vendor();
        $vendor->user_id = $user->id;
        $vendor->store_name = $request->store_name;
        $vendor->store_address = $request->store_address;
        $vendor->country_code = $request->country_code;
        $vendor->phone = $request->phone;

        if ($request->country_code === 'HU') {
            $vendor->nav_user_id = $request->nav_user_id;
            $vendor->nav_exchange_key = $request->nav_exchange_key;
        }

        if ($request->hasFile('anaf_pfx') && $request->country_code === 'RO') {
            $path = $request->file('anaf_pfx')->store('anaf-certificates', 'private');
            $vendor->anaf_pfx_path = $path;
        }

        if ($isNewVendor) {
            $vendor->status = VendorStatusEnum::Pending->value;
        }

        $vendor->save();

        if ($isNewVendor) {
            $user->assignRole(RolesEnum::Vendor);
            Mail::to(config('mail.admin'))->send(new NewVendorRequest($vendor));
            return back()->with('success', 'Your vendor request has been submitted and is pending approval.');
        }

        return back()->with('success', 'Your vendor details have been updated.');
    }

    public function details()
    {
        return Inertia::render('Vendor/Details');
    }
}

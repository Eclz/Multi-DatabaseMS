<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use DB;

class ContactsController extends Controller
{
    public function index()
    {
        // dd(Auth::user()->account->contacts());

        
        $data = DB::connection('mysql')->table('contacts')->select(['id','package','carbin','trail','selection_name','deleted_at'])->paginate(10);
        // dd($data);
        return Inertia::render('Contacts/Index', [
            'filters' => Request::all('search', 'trashed'),
            'contacts' => Contact::orderBy('id')
                ->filter(Request::only('search', 'trashed'))
                ->paginate(10)
                ->withQueryString(),
            // 'contacts'=>$data,
        ]);
    }

    public function create()
    {
        $database = DB::connection('mysql')->table('clients')->select(['dname','clientName','id'])->get();

        return Inertia::render('Contacts/Create', [
            'organizations' => Auth::user()->account
                ->organizations()
                ->orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
            'selections' => $database,
        ]);
    }

    public function store()
    {
        Request::validate([
            'package' => ['required'],
            'carbin' => ['required_without_all:trail'],
            'trail' => ['required_without_all:carbin'],
            'selection_id' => ['required'],
            'currency'=>['required'],
            'activate'=>['required'],
            'deactivate'=>['required'],
        ],
        [
            'selection_id.required'=>'You must select a Client To bill',
            'package.required'=>'You must select a billing Plane',
            'carbin.required_without_all'=>'A carbin Head is required if you have not selected the Truck trailer',
            'trail.required_without_all'=>'A carbin Trailer is required if you have not selected the Truck Head',
            'currency.required' => 'Enter currency symbol',
            'activate.required' => 'Enter activation period',
            'deactivate.required' => 'Enter de-activation period',
        ]);

        $user = DB::connection('mysql')->table('clients')->where('id',Request::get('selection_id'))->select('clientName')->first();

        Contact::create([
            'selection_id'  => Request::get('selection_id'),
            'package'       => Request::get('package'),
            'carbin'        => Request::get('carbin'),
            'trail'         => Request::get('trail'),
            'selection_name'=> $user->clientName,
            'currency'      => Request::get('currency'),
            'activate'      => Request::get('activate'),
            'deactivate'    => Request::get('deactivate'),
        ]);

        return Redirect::route('contacts')->with('success', 'Contact created.');
    }

    public function edit(Contact $contact)
    {
        $database = DB::connection('mysql')->table('clients')->select(['dname','clientName','id'])->get();

        return Inertia::render('Contacts/Edit', [
            'contact' => [
                'id' => $contact->id,
                'selection_id' => $contact->selection_id,
                'selection_name' => $contact->selection_name,
                'organization_id' => $contact->organization_id,
                'package'    => $contact->package,
                'carbin'     => $contact->carbin,
                'currency'   => $contact->currency,
                'trail'      => $contact->trail,
                'deleted_at' => $contact->deleted_at,
                'activate'   => $contact->activate,
                'deactivate' => $contact->deactivate,
            ],
            'selections'=>$database,
            'organizations' => Auth::user()->account->organizations()
                ->orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
        ]);
    }

    public function update(Contact $contact)
    {
        Request::validate([
            'package' => ['required'],
            'carbin' => ['required_without_all:trail'],
            'trail' => ['required_without_all:carbin'],
            'selection_id' => ['required'],
            'activate'=>['required'],
            'deactivate'=>['required'],
            'currency'=>['required'],
        ],
        [
            'selection_id.required'=>'You must select a Client To bill',
            'package.required'=>'You must select a billing Plane',
            'currency.required' => 'Enter currency symbol',
            'carbin.required_without_all'=>'A carbin Head is required if you have not selected the Truck trailer',
            'trail.required_without_all'=>'A carbin Trailer is required if you have not selected the Truck Head',
            'activate.required' => 'Enter activation period',
            'deactivate.required' => 'Enter de-activation period',
        ]);

        $user = DB::connection('mysql')->table('clients')->where('id',Request::get('selection_id'))->select('clientName')->first();

        $contact->update([
            'selection_id'  => Request::get('selection_id'),
            'package'       => Request::get('package'),
            'carbin'        => Request::get('carbin'),
            'trail'         => Request::get('trail'),
            'currency'      => Request::get('currency'),
            'selection_name'=> $user->clientName,
            'activate'      => Request::get('activate'),
            'deactivate'    => Request::get('deactivate'),
        ]);

        // Rule::exists('organizations', 'id')->where(fn ($query) => $query->where('account_id', Auth::user()->account_id)),


        return Redirect::back()->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return Redirect::back()->with('success', 'Billing Package trashed.');
    }

    public function restore(Contact $contact)
    {
        $contact->restore();

        return Redirect::back()->with('success', 'Billing Package restored.');
    }
}

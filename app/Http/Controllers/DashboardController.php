<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Request;
use App\Models\Client;
use Inertia\Inertia;
use DB;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {

        $pie = DB::connection('mysql')->table('clients')->selectRaw('SUM(trailers) + SUM(Heads) AS Total')->selectRaw('clientName as name')->groupByRaw('name')->get();

        $piechart[] = ['Client', 'Assets'];

        foreach($pie as $key => $value)
        {
          $piechart[++$key] = [$value->name, intval($value->Total)];
        }

        $line = Payment::whereRaw('year(`date_renewed`) = ?', array(date('Y')))
        ->selectRaw('MONTH(date_renewed) as month,selection_id as identifier,SUM(paid) as Total,COUNT(id) as Quantity, platenumber as platenumber,date_renewed as renewed,client as clientName')
        ->groupBy('identifier')
        ->orderBy('month')
        ->get();

        $linechart[] = ['Month', 'Expense','Qunatity(Ltrs)'];
        foreach($line as $key => $value)
        {
           $linechart[++$key] = [date("F", mktime(0, 0, 0, $value->month, 10)), intval($value->Total), intval($value->Quantity)];
        }
        
                // dd($linechart);

        // dd($piechart);

        return Inertia::render('Dashboard/Index',['pie'=>$piechart]);
    }

    
    // public function index()
    // {

    //     $data = DB::connection('mysql')->table('clients')->get();
       
    //     return Inertia::render('Organizations/Index', [
    //         'filters' => Request::all('search', 'trashed'),
    //         'final' => Client::orderBy('id')
    //                     ->filter(Request::only('search', 'trashed'))
    //                     ->paginate(10)
    //                     ->withQueryString(),
    //     ]);
    // }
}

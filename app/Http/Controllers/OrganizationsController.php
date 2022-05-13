<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use Codedge\Fpdf\Fpdf\Fpdf;
use Inertia\Inertia;
use DB;
use Carbon\Carbon;


class OrganizationsController extends Controller
{
    public function index()
    {

        $data = DB::connection('mysql')->table('clients')->get();
       
        return Inertia::render('Organizations/Index', [
            'filters' => Request::all('search', 'trashed'),
            'final' => Client::orderBy('updated_at','desc')
                        ->filter(Request::only('search', 'trashed'))
                        ->paginate(10)
                        ->withQueryString(),
        ]);
    }

    public function create()
    {
        $settings = DB::connection('mysql')->table('setup')->select(['identifier','DB','id'])->get();
        // return Inertia::render('Organizations/Create');
        return Inertia::render('Organizations/Create',[
          'settings'=>$settings,
        ]);
    }

    public function activate(Request $activate)
    {
        
        
        Request::validate(['activity' => ['required'],],['activity.required' => 'An action is needed to be selected with atleast one asset checked' ]);

        $activate = Request::get('selected');
        $id = Request::get('name');
        $state = Request::get('activity');
        $billing = DB::connection('mysql')->table('contacts')->where('selection_id',$id)->select(['package','carbin','trail','activate','deactivate'])->first();
        
       
        if($billing->package == 31){
            $bill = 1;
        }elseif($billing->package == 122){
            $bill = 4;
        }else{
            $bill = 12;
        }

        
        $database = DB::connection('mysql')->table('clients')->where('id',$id)->select(['dname','clientName'])->first();

        
        foreach($activate as $act){
            $enddate =DB::connection($database->dname)->table('vehicles')->where('id',$act)->select(['licienseto','vehicle','trailerstate'])->first('licienseto');
            

            if($enddate->licienseto ==''){
                 $finaldate = Carbon::now()->toDateString();
            }else{
                $finaldate = $enddate->licienseto;
            }
            if($state == 2){   
                if($enddate->trailerstate == 0){
                    $paid = $bill*$billing->carbin;
                }else{
                    $paid = $bill*$billing->carbin;
                }         
                $date = Carbon::parse($finaldate)->modify('+'.$billing->package.'days')->toDateString();

                DB::connection($database->dname)
                    ->table('vehicles')
                    ->where('id',$act)
                    ->update([
                        'setting'      =>$state,
                        'liciensefrom' =>$finaldate,
                        'licienseto'   =>$date,
                    ]);
                
                DB::connection('mysql')
                    ->table('payments')
                    ->insert([
                        'selection_id'=>$id,
                        'client'      =>$database->clientName,
                        'platenumber' =>$enddate->vehicle,
                        'date_renewed'=>Carbon::now()->toDateString(),
                        'trailerstate'=>$enddate->trailerstate,
                        'paid'        =>$paid,
                        'period'      =>$billing->package,
                        
                    ]);
            }else{
                if($state == 1){
                   $date = Carbon::parse($finaldate)->modify('+'.$billing->activate.'days')->toDateString();
                }else{
                   $date = Carbon::parse($finaldate)->modify('-'.$billing->deactivate.'days')->toDateString();
                }

                DB::connection($database->dname)
                ->table('vehicles')
                ->where('id',$act)
                ->update(['setting'=>$state,'licienseto' =>$date,'liciensefrom' =>$finaldate,'setting'=>$state,]);
            }
                    

        }
        
        return Redirect::back()->with('success', 'Asset Status Updated.');
    }


    public function store()
    {

        Request::validate([
            'name' => ['required', 'max:200'],
            'tin' => ['required','numeric','min:10'],
            'phone' => ['required'],
            'address' => ['required','max:150'],
        ]);

        $client = DB::connection('mysql')->table('setup')->where('id',Request::get('name'))->select(['identifier','DB'])->first();

        $heads = DB::connection($client->DB)->table('vehicles')->where('trailerstate',0)->count();
        
        $trails = DB::connection($client->DB)->table('vehicles')->where('trailerstate',1)->count();

        $ident = substr($client->identifier,0,3);

        if(Client::where('clientName',$client->identifier)->count()>0){
            throw ValidationException::withMessages(['name' => 'Client has already been onboarded,Please choose another client name']);
        }else{
            Client::Create([
                'clientName' => $client->identifier,
                'Heads'      => $heads,
                'trailers'   => $trails,
                'dname'      => $client->DB,
                'phone'      => Request::get('phone'),
                'pobox'      => Request::get('address'),
                'tin'        => Request::get('tin'),
                'invoice'    => $ident.'0',
            ]);
    
        }
        
        return Redirect::route('organizations')->with('success', 'Organization created.');
    }

    public function edit(Client $client)
    {
        
        $database = DB::connection('mysql')->table('clients')->where('id',$client->id)->select(['dname','clientName'])->first();

        
        
        $date = Carbon::now()->modify('+10 days')->toDateString();
        $today = Carbon::now()->toDateString();

        $data = DB::connection($database->dname)
                    ->table('vehicles')
                    ->select('id','liciensefrom','licienseto','vehicle','trailerstate','setting')
                    ->where('licienseto', '<=', $date)
                    ->where('licienseto', '>=', $today)
                    ->orWhere('licienseto', '<=', $today)
                    ->orwhere('setting',4)
                    ->orderBy('created_at','desc')
                    ->paginate(10)
                    ->withQueryString();

        return Inertia::render('Organizations/Edit', [
            'organization' => [
                'id' => $client->id,
                'name' => $client->clientName,
                'email' => $client->start,
                'phone' => $client->end,
                'deleted_at' => $client->deleted_at,
                'asset' => $data,
            ],
        ]);
    }

    public function update(Client $client)
    {

        
        $date = Carbon::now()->modify('+10 days')->toDateString();
        $today = Carbon::now()->toDateString();
        $statehead = 0;
        $statetrail = 1;

        $Number = $client['invoice'];
        $increase = substr($Number, 3);
       
        $newvalue = $increase + 1;
        $ident = substr($Number,0,3);
        $final = $ident.''.$newvalue;


        $head = DB::connection($client->dname)
                ->table('vehicles')
                ->select('id','liciensefrom','licienseto','vehicle','trailerstate','setting')
                // ->where('licienseto', '<=', $date)
                // ->where('licienseto', '>=', $today)
                // ->where('trailerstate',$statehead)
                ->Where('licienseto', '<=', $today)
                ->where('trailerstate',$statehead)
                ->get();

        $trail= DB::connection($client->dname)
                ->table('vehicles')
                ->select('id','liciensefrom','licienseto','vehicle','trailerstate','setting')
                // ->where('licienseto', '<=', $date)
                // ->where('licienseto', '>=', $today)
                // ->where('trailerstate',$statetrail)
                ->Where('licienseto', '<=', $today)
                ->where('trailerstate',$statetrail)
                ->get();

        $costs = DB::connection('mysql')->table('contacts')->where('selection_id',$client->id)->select(['carbin','trail','package','currency'])->first();

        $currency = $costs->currency;
        $normal = count($head);
        $normaltail = count($trail);

        $package = '';

        if($costs->package == 31){
            $package = 'Monthly Renewal fees';
        }elseif($costs->package == 122){
            $package = 'Quarterly Renewal fees';
        }else{
            $package = 'Annually Renewal fees';
        }
      
        $headcost = $costs->carbin * $normal;
        $trailcost = $costs->trail * $normaltail;
        
        $total = $headcost + $headcost;

        $client->update([
          'invoice' => $final,
        ]);

        $pdf = new FPDF;
        $pdf->AddPage('');
        $pdf->Ln(10);

        // $pdf->Cell(-7,6,'',0,0,'L');
        $pdf->Image(public_path('images/car.png'),1,1,40);
        $pdf->Cell(170,5,'', 0,1,'R');

        $pdf->Ln(10);


        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(5,5,'','',0,'L');
        $pdf->Cell(80,5,'Kampala Office','',0,'L');
        $pdf->Cell(75,5,'Nairobi Office','',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',9);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(5,5,'','',0,'L');
        $pdf->Cell(80,5,'MNS House, Rm 7B, Portbell Road','',0,'L');
        $pdf->Cell(75,5,'Vision Plaza, 3rd Floor Suite 7D, Mombasa Road','',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',9);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(5,5,'','',0,'L');
        $pdf->Cell(80,5,'P.O.Box 2359 Kampala, Tel: +256 414 580632','',0,'L');
        $pdf->Cell(75,5,'P.O.Box 56630-00200 Nairobi, Tel: +254 202 303555','',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',9);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(5,5,'','',0,'L');
        $pdf->Cell(80,5,'Email:accounts@smartwatchsolutions.com','',0,'L');
        $pdf->Cell(75,5,'Website:www.smartwatchsolutions.com','',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(15);


        $pdf->SetFont('Arial','B',12);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(80,5,'','',0,'L');
        $pdf->Cell(35,5,'Tax Invoice','',0,'L');
        $pdf->Cell(40,5,'','',0,'L');
        $pdf->Cell(30,5,'','',0,'L');
        $pdf->Cell(20,5,'',2,1,'L');

        

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(155,5,'To','',0,'L');
        $pdf->Cell(30,5,'','',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        // $pdf->Cell(25,5,'','',0,'L');
        $pdf->Cell(125,5,$client->clientName,'',0,'L');
        $pdf->Cell(10,5,'Date','',0,'L');
        $pdf->Cell(50,5,\Carbon\Carbon::now()->format('d F Y'),'',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        // $pdf->Cell(25,5,'','',0,'L');
        $pdf->Cell(125,5,$client->phone,'',0,'L');
        $pdf->Cell(15,5,'Invoice #:','',0,'L');
        $pdf->Cell(45,5,$final,'',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(5);
 

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(25,5,'','',0,'L');
        $pdf->Cell(100,5,'','',0,'L');
        $pdf->Cell(10,5,'P.O #:','',0,'L');
        $pdf->Cell(50,5,$client->pobox,'',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(25,5,'','',0,'L');
        $pdf->Cell(100,5,'','',0,'L');
        $pdf->Cell(10,5,'TIN:','',0,'L');
        $pdf->Cell(50,5,$client->tin,'',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(10);
 


        $pdf->SetFont('Arial','B',9);
        $pdf->setFillColor(37,89,54);
        $pdf->SetTextColor(255,255,255);
       
        $pdf->Cell(10,5,'No','BTLR',0,'L',1);
        $pdf->Cell(100,5,'Description ( ' .$package. ' ) ','BTLR',0,'L',1);
        $pdf->Cell(15,5,'Qty','BTLR',0,'L',1);
        $pdf->Cell(30,5,'Unit Price( ' .$currency. ' )','BTLR',0,'L',1);
        $pdf->Cell(30,5,'Total Price( ' .$currency. ' )','BTLR',1,'L',1);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',9);


        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(110,5,'Trailer Heads','LBRT',0,'L');
        $pdf->Cell(15,5,number_format($normal,0),'LBRT',0,'L');
        $pdf->Cell(30,5,number_format($costs->carbin,2),'LBRT',0,'L');
        $pdf->Cell(30,5,number_format($headcost, 2),'LBRT',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        
        $pdf->Ln(5);



        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(110,5,'Trailer Tails','LBRT',0,'L');
        $pdf->Cell(15,5,number_format($normaltail,0),'LBRT',0,'L');
        $pdf->Cell(30,5,number_format($costs->trail,2),'LBRT',0,'L');
        $pdf->Cell(30,5,number_format($trailcost, 2),'LBRT',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(236,28,36);
        $pdf->Cell(125,5,'','',0,'L');
        $pdf->Cell(30,5,'Overroll Total','LBTR',0,'L');
        $pdf->Cell(30,5,number_format($total, 2),'LBRT',1,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',9);
        $pdf->setFillColor(255,255,255);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(185,5,'Banking Details:','',0,'',1);
        $pdf->Cell(20,5,'',2,1,'L');

        $pdf->SetFont('Arial','',9);
        $pdf->setFillColor(255,255,255);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(185,5,'Bank: Stanbic Bank','',0,'',1);
        $pdf->Cell(20,5,'',2,1,'L');
        

        $pdf->SetFont('Arial','',9);
        $pdf->setFillColor(255,255,255);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(185,5,'A/C Name;Smartwatch Solutions Ltd','',0,'',1);
        $pdf->Cell(20,5,'',2,1,'L');
        
        $pdf->SetFont('Arial','',9);
        $pdf->setFillColor(255,255,255);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(185,5,'A/C No ;9030016540675','',0,'',1);
        $pdf->Cell(20,5,'',2,1,'L');
        
        $pdf->SetFont('Arial','',9);
        $pdf->setFillColor(255,255,255);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(185,5,'Swift Code;SBICUGKX','',0,'',1);
        $pdf->Cell(20,5,'',2,1,'L');
        
        $pdf->AddPage('');

        $pdf->SetFont('Arial','B',15);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(80,5,'','',0,'L');
        $pdf->Cell(35,5,'Trailer Tails','',0,'L');
        $pdf->Cell(40,5,'','',0,'L');
        $pdf->Cell(30,5,'','',0,'L');
        $pdf->Cell(20,5,'',2,1,'L');


        $pdf->SetFont('Arial','B',9);
        $pdf->setFillColor(37,89,54);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(10,5,'No','BTLR',0,'L',1);
        $pdf->Cell(185,5,'Tails','BTLR',1,'L',1);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',9);
        
        $i = 1;

        foreach($head as $head){
            $pdf->SetFont('Arial','B',9);
            $pdf->setFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(10,5,$i,'BTLR',0,'L',1);
            $pdf->Cell(185,5,$head->vehicle,'BTLR',1,'L',1);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('Arial','',9);
            $i++;
        }


        $pdf->AddPage('');

        $pdf->SetFont('Arial','B',15);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(80,5,'','',0,'L');
        $pdf->Cell(35,5,'Trailer Heads','',0,'L');
        $pdf->Cell(40,5,'','',0,'L');
        $pdf->Cell(30,5,'','',0,'L');
        $pdf->Cell(20,5,'',2,1,'L');


        $pdf->SetFont('Arial','B',9);
        $pdf->setFillColor(37,89,54);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(10,5,'No','BTLR',0,'L',1);
        $pdf->Cell(185,5,'Head','BTLR',1,'L',1);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',9);
        
        $x = 1;

        foreach($trail as $trail){
            $pdf->SetFont('Arial','B',9);
            $pdf->setFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(10,5,$x,'BTLR',0,'L',1);
            $pdf->Cell(185,5,$trail->vehicle,'BTLR',1,'L',1);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('Arial','',9);
            $x++;
        }

        


        $pdf->Output('D', $client->clientName.' Invoice Generated On '.\Carbon\Carbon::now()->format('d F Y').'.pdf', true);
        

        // return Redirect::back()->with('success', 'Organization updated.');
    }

    public function destroy(Client $client)
    {

        $client->delete();

        DB::connection($client->dname)->table('users')->where('active',1)->where('support',0)->update(['active'=>0]);  

        return Redirect::back()->with('success', 'Client de-activated.');
    }

    public function restore(Client $client)
    {
        $client->restore();

        DB::connection($client->dname)->table('users')->where('active',0)->where('support',0)->update(['active'=>1]);

        return Redirect::back()->with('success', 'Client restored.');
    }
}

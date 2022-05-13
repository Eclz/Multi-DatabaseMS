<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use DB;
use Carbon\Carbon;
use Codedge\Fpdf\Fpdf\Fpdf;
use AmrShawky\Currency\Facade\Currency;

class ReportsController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function active()
    {
        $settings = DB::connection('mysql')->table('clients')->select(['clientName','id','dname'])->get();
        return Inertia::render('Reports/active',[
            'settings'=>$settings,
          ]);
    }

    public function ExpectedRevenue()
    {
        $settings = DB::connection('mysql')->table('clients')->select(['clientName','id','dname'])->get();
        return Inertia::render('Reports/revenue',[
            'settings'=>$settings,
          ]);
    }

    public function activereport($id)
    {

    
        $database = DB::connection('mysql')->table('clients')->where('dname',$id)->select('clientName')->first();

        $today = Carbon::now()->toDateString();
        
        $data = DB::connection($id)
        ->table('vehicles')
        ->select('id','liciensefrom','licienseto','vehicle','trailerstate','setting')
        ->where('licienseto', '>=', $today)
        ->orderBy('licienseto','desc')
        ->get();

        $pdf = new FPDF;
        $pdf->AddPage('');
        $pdf->Ln(10);

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


        $pdf->SetFont('Arial','B',15);
        $pdf->SetTextColor(37,89,54);
        // $pdf->Cell(80,5,'','',0,'L');
        $pdf->Cell(105,5,'Active Asset for '.$database->clientName,'',0,'L');
        $pdf->Cell(40,5,'','',0,'L');
        $pdf->Cell(30,5,'','',0,'L');
        $pdf->Cell(20,5,'',2,1,'L');

        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',9);
        $pdf->setFillColor(37,89,54);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(10,5,'No','BTLR',0,'L',1);
        $pdf->Cell(50,5,'Plate No.','BTLR',0,'L',1);
        $pdf->Cell(40,5,'Asset Part.','BTLR',0,'L',1);
        $pdf->Cell(95,5,'Licence Expiration Date','BTLR',1,'L',1);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',9);
        
        $i = 1;

        foreach($data as $data){
            if($data->trailerstate == 0){
                $type = "Carbin";
            }else{
                $type = "Trailer";
            }
            $pdf->SetFont('Arial','B',9);
            $pdf->setFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(10,5,$i,'BTLR',0,'L',1);
            $pdf->Cell(50,5,$data->vehicle,'BTLR',0,'L',1);
            $pdf->Cell(40,5,$type,'BTLR',0,'L',1);
            $pdf->Cell(95,5,Carbon::parse($data->licienseto)->format('d F Y'),'BTLR',1,'L',1);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('Arial','',9);
            $i++;
        }



        $pdf->Output('D','Active Assets for ' .$database->clientName. ' Generated On '.\Carbon\Carbon::now()->format('d F Y').'.pdf', true);

    }

    public function revenuereport($id){

        $data = DB::connection('mysql')->table('clients')->where('clients.dname',$id)->join('contacts', 'clients.id', '=', 'contacts.selection_id')->select('clients.*', 'contacts.currency', 'contacts.carbin','contacts.trail','contacts.package')->first();

        if($data->package == 31){
            $package = 'Monthly Renewal fees';
        }elseif($data->package == 122){
            $package = 'Quarterly Renewal fees';
        }else{
            $package = 'Annually Renewal fees';
        }

        $total = $data->Heads*$data->carbin + $data->trailers*$data->trail;

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
        // $pdf->Cell(80,5,'','',0,'L');
        $pdf->Cell(115,5,'Report For Expected Revenue For','',0,'L');
        $pdf->Cell(40,5,'','',0,'L');
        $pdf->Cell(30,5,'','',0,'L');
        $pdf->Cell(20,5,'',2,1,'L');

        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        // $pdf->Cell(25,5,'','',0,'L');
        $pdf->Cell(125,5,$data->clientName,'',0,'L');
        $pdf->Cell(10,5,'Date','',0,'L');
        $pdf->Cell(50,5,\Carbon\Carbon::now()->format('d F Y'),'',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        // $pdf->Cell(25,5,'','',0,'L');
        $pdf->Cell(125,5,$data->phone,'',0,'L');
        // $pdf->Cell(15,5,'Invoice #:','',0,'L');
        // $pdf->Cell(45,5,$final,'',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(5);
 

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(25,5,'','',0,'L');
        $pdf->Cell(100,5,'','',0,'L');
        $pdf->Cell(10,5,'P.O #:','',0,'L');
        $pdf->Cell(50,5,$data->pobox,'',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(25,5,'','',0,'L');
        $pdf->Cell(100,5,'','',0,'L');
        $pdf->Cell(10,5,'TIN:','',0,'L');
        $pdf->Cell(50,5,$data->tin,'',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(10);
 


        $pdf->SetFont('Arial','B',9);
        $pdf->setFillColor(37,89,54);
        $pdf->SetTextColor(255,255,255);
       
        $pdf->Cell(10,5,'No','BTLR',0,'L',1);
        $pdf->Cell(100,5,'Description ( ' .$package. ' ) ','BTLR',0,'L',1);
        $pdf->Cell(15,5,'Qty','BTLR',0,'L',1);
        $pdf->Cell(30,5,'Unit Price( ' .$data->currency. ' )','BTLR',0,'L',1);
        $pdf->Cell(30,5,'Total Price( ' .$data->currency. ' )','BTLR',1,'L',1);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',9);


        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(110,5,'Trailer Heads','LBRT',0,'L');
        $pdf->Cell(15,5,number_format($data->Heads,0),'LBRT',0,'L');
        $pdf->Cell(30,5,number_format($data->carbin,2),'LBRT',0,'L');
        $pdf->Cell(30,5,number_format($data->Heads*$data->carbin, 2),'LBRT',0,'L');
        $pdf->Cell(20,5,'',0,0,'R');
        $pdf->SetTextColor(0,0,0);

        
        $pdf->Ln(5);



        $pdf->SetFont('Arial','B',9);
        $pdf->SetTextColor(37,89,54);
        $pdf->Cell(110,5,'Trailer Tails','LBRT',0,'L');
        $pdf->Cell(15,5,number_format($data->trailers,0),'LBRT',0,'L');
        $pdf->Cell(30,5,number_format($data->trail,2),'LBRT',0,'L');
        $pdf->Cell(30,5,number_format($data->trailers*$data->trail, 2),'LBRT',0,'L');
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

        $pdf->Output('D', $data->clientName.' Invoice Generated On '.\Carbon\Carbon::now()->format('d F Y').'.pdf', true);
    }
}

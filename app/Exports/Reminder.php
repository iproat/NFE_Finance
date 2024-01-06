<?php

namespace App\Console\Commands;

use DateTime;
use App\Model\GeneralSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class Reminder extends Command
{

    protected $signature = 'reminder';

    protected $description = 'Reminder';


    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        set_time_limit(0);
        
        $date=DATE('Y-m-d');
        $reminder = DB::table('reminder')
            ->where('status',1)
            ->whereRaw(' 
                        ( DATE_SUB(expiry_date,INTERVAL 1 MONTH)  <= "'.$date.'" AND expiry_date IS NOT NULL )

                    ')->get();
        $msg='';
        $ex_info=$info=[];

        //dd($reminder);

        foreach ($reminder as $key => $Data) {
            if( DATE('Y-m-d',strtotime($Data->expiry_date."-1 MONTHS" ))  <= $date && $Data->expiry_date >= $date   ){
                $info[]=$this->expire($Data->title,$Data->content,$Data->expiry_date);
            }elseif(!is_null($Data->expiry_date) && DATE('Y-m-d',strtotime($Data->expiry_date."-1 MONTHS" ))  <= $date){
                $ex_info[]=$this->expire($Data->title,$Data->content,$Data->expiry_date);
            }
        }

        $set=GeneralSettings::find(1);
        $data=['name'=>'','content'=>$info,'expired_content'=>$ex_info];
        if($set->email_ids){
            foreach(explode(",",$set->email_ids) as $email_Data){
                $this->mail($data,$email_Data,$set);
            }

        }

        //dd($allEmployee);
        
    }

    function days($from_date,$to_date){
        $date1 = new DateTime($from_date);
        $date2 = new DateTime($to_date);
        $days  = $date2->diff($date1)->format('%a');
        return $days;
    }

    function expire($title,$conent,$date){
        return ['title'=>$title,'content'=>$conent,'date'=>DATE('d-m-Y',strtotime($date)),'days'=>$this->days($date,DATE('Y-m-d'))];
    }

    function mail($data,$to,$set){
        Mail::send(['html'=>'officemanagement_mail'], $data, function($message)use($to,$data,$set) {
             $message->to($to,$data['name'])->subject($set->officedoc_mail_subject);
             $message->from($set->officedoc_sender_mail,$set->officedoc_sender_name);
        });
    }

    
}

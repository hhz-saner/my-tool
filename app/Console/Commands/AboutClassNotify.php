<?php

namespace App\Console\Commands;

use App\Events\SendSMS;
use App\Services\AboutClassServices;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Cache;
use App\Models\AboutClass;


class AboutClassNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aboutClass';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'aboutClass';


    private $aboutClassService;


    /**
     * AboutClass constructor.
     * @param AboutClassServices $aboutClassService
     *
     */
    public function __construct(AboutClassServices $aboutClassService)
    {
        parent::__construct();
        $this->aboutClassService = $aboutClassService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $hasClass = [];
        $aboutClass = AboutClass::where('date', '>=', Carbon::now())->where('status', 0)->get();
        foreach ($aboutClass as $class) {
            $className = last(explode(' ', $class->class));
            if ($this->hasClass($class->date, $className)) {
                $hasClass[$class->date][] = $className;
                $class->status = 1;
                $class->save();
            }
        }
        if (!empty($hasClass)) {
            $sendMessage = "\r\n";
            foreach ($hasClass as $date => $class) {
                $sendMessage .= $date . ' ';
                foreach ($class as $val) {
                    $sendMessage .= $val . ' ';
                }
                $sendMessage .= "\r\n";
            }
            event(new SendSMS(config('aboutClass.phone'), ['Aliyun' => 'SMS_115380856'], ['data' => $sendMessage]));
        }
    }

    private function hasClass($date, $className)
    {
        $data = $this->aboutClassService->aboutClassList($date, false);
        foreach ($data as $value) {
            if ($value['class_name'] == $className && $value['status'] == 1) {
                echo $date.'  '.$className.'  '.$value['remain_num']."\r\n";
                return true;
            }
        }
        return false;
    }
}

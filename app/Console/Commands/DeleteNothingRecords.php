<?php

namespace App\Console\Commands;

use App\Models\RecordContent;
use App\Models\RecordState;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteNothingRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:deleteNothingRecords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1日立って重量・セットがないレコードを削除します。';

    private $diff_day = 1;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(RecordContent $recordContent, RecordState $recordState)
    {
        $now = Carbon::now();
        $recordContents = $recordContent->get();
        $recordStates = $recordState->get();
        foreach($recordContents as $recordContent){
            if(empty($recordContent->weight) && 
               empty($recordContent->rep) && 
               empty($recordContent->right_weight) &&
               empty($recordContent->right_rep) &&
               empty($recordContent->left_weight) &&
               empty($recordContent->left_rep)
            ){
                $TgtRecordState = $recordState->where('id', $recordContent->record_state_id)->first();
                if(isset($TgtRecordState)){
                    if($now->diffInDays($TgtRecordState->recorded_at) >= $this->diff_day){
                        $TgtRecordState->delete();
                    }
                }
            }
        }
        foreach($recordStates as $recordState){
            // メニュー選択記録があるか
            if(($recordState->recordMenus->isEmpty()) && ($now->diffInDays($recordState->recorded_at) >= $this->diff_day)){
                $recordState->delete();
            }
            // 個々のメニューにて記録があるか
            if(($recordState->recordContents->isEmpty()) && ($now->diffInDays($recordState->recorded_at) >= $this->diff_day)){
                $recordState->delete();
            }
        }

        return Command::SUCCESS;
    }
}

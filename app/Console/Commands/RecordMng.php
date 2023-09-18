<?php

namespace App\Console\Commands;

use App\Models\Record;
use App\Models\RecordContent;
use App\Models\RecordState;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecordMng extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:deleteRecords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重量もしくは体重にデータがないものは１日おきに削除する。';

    private $day_hour = 24;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(RecordState $recordState, RecordContent $recordContent)
    {
        $now = Carbon::now();
        $delete_flg = 0;
        $recordStates = $recordState->get();
        foreach($recordStates as $record){
            if(isset($record->updated_at)){
                $updateTime = new Carbon($record->updated_at);
                // 他で定義しているものを使用する場合は'$this->'が必要
                //記録開始して１日経ったら
                if($now->diffInHours($updateTime) >= $this->day_hour){
                    $delete_flg = 1;
                }
            }else{
                $createTime = new Carbon($record->created_at);
                // 他で定義しているものを使用する場合は'$this->'が必要
                //記録開始して１日経ったら
                if($now->diffInHours($createTime) >= $this->day_hour){
                    $delete_flg = 1;
                }
            }
            if($delete_flg === 1){
                // １日経過し重さを記録されていない記録は削除する
                $this->deleteRec($record);
            }
        }

        return Command::SUCCESS;
    }

    public function deleteRec($targetRecState)
    {
        $RecContents = $targetRecState->recordContents()->get();
        foreach($RecContents as $RecContent){
            $menu = $RecContent->menu()->first();
            if(isset($menu)){
                if($menu->oneSide === 0){
                    if((!isset($targetRecState->bodyWeight)) && (!isset($RecContent->weight))){
                        $targetRecState->delete();
                    }
                }elseif($menu->oneSide === 1){
                    // 片方ずつ記録する場合
                    if((!isset($targetRecState->bodyWeight)) && ((!isset($RecContent->left_weight)) || (!isset($RecContent->right_weight)))){
                        $targetRecState->delete();
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}

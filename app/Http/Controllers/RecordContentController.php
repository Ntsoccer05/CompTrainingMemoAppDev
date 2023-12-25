<?php

namespace App\Http\Controllers;

use App\Models\RecordMenu;
use App\Models\RecordContent;
use App\Models\RecordState;
use Illuminate\Http\Request;
use stdClass;

class RecordContentController extends Controller
{
    public function index(Request $request, RecordMenu $recordMenu, RecordState $recordState){
        $tgtRecords = Null;

        $user_id = $request->user_id;
        $category_id = $request->category_id;
        $menu_id = $request->menu_id;
        $record_state_id = $request->record_state_id;
        $recorded_at = $request->recorded_at;
        $recordContents=[];
        $recordContent=[];
        $menu=[];
        $category=[];

        if(!$category_id && !$recorded_at){
            //親テーブルがあると子供のデータ取得時に呼び出した親のデータも一緒に取得できる。
            $records = $recordState->where('user_id', $user_id)->get();
            //記録日の重複削除
            $records = $records->unique('recorded_at');
            foreach($records as $record){
                // 初期化(初期化しないと前回のデータに追加されてしまうため)
                $recordContent=[];
                $menu = [];
                $category = [];

                $recordMenus = $record->recordMenus;
                $recorded_at = [
                    "record_id"=>$record->id,
                    "recorded_at"=>$record->recorded_at
                ];
                $hasRecordMenu = $recordMenu->where('record_state_id', $record->id)->exists();
                $tgtRecordMenu = $recordMenu->where('record_state_id', $record->id)->get();
                $recordContent['recorded_at']=$recorded_at;
                // メニュー登録がある場合
                if($hasRecordMenu){
                    foreach($tgtRecordMenu as $recordMenuContent){
                            $menuContent = $recordMenuContent->menu->content;
                            $menuId = $recordMenuContent->menu_id;
                            $categoryContent = $recordMenuContent->category->content;
                            $categoryId = $recordMenuContent->category_id;
                            $menu[] = [
                                "menu_id"=>$menuId,
                                "menu_content"=>$menuContent
                            ];
                            $category[] = [
                                "category_id"=>$categoryId,
                                "category_content"=>$categoryContent
                            ];
                            // array_unique：重複を削除
                            $menu = array_unique($menu, SORT_REGULAR);
                            $category = array_unique($category, SORT_REGULAR);
                            $recordContent['menu']=$menu;
                            $recordContent['category']=$category;
                        }
                }
                $recordContents[] = $recordContent;
            }
            return response()->json(["status_code" => 200, "message" => "記録した全てのデータを取得", 'records'=>$recordContents]);
        }
        if(isset($recorded_at)){
            $record = $recordState->where('user_id', $user_id)->where('recorded_at', $recorded_at)->first();
            // 初期化
            $recordContent=[];
            $recorded_at = [
                "record_id"=>$record->id,
                "recorded_at"=>$record->recorded_at
            ];
            $hasRecordMenu = $recordMenu->where('record_state_id', $record->id)->exists();
            $recordContent['recorded_at']=$recorded_at;
            // メニュー登録がある場合
            if($hasRecordMenu){
                $recordMenus = $record->recordMenus;
                foreach($recordMenus as $i =>$recordMenu){
                    $menuContent = $recordMenu->menu->content;
                    $menuId = $recordMenu->menu_id;
                    $categoryContent = $recordMenu->category->content;
                    $categoryId = $recordMenu->category_id;
                    $menu[] = [
                        "menu_id"=>$menuId,
                        "menu_content"=>$menuContent
                    ];
                    $category[] = [
                        "category_id"=>$categoryId,
                        "category_content"=>$categoryContent
                    ];
                    // array_unique：重複を削除
                    $menu = array_unique($menu, SORT_REGULAR);
                    $category = array_unique($category, SORT_REGULAR);
                    $recordContent['menu']=$menu;
                    $recordContent['category']=$category;
                }
            }
            $recordContents[] = $recordContent;
            return response()->json(["status_code" => 200, "message" => "選択した日付のデータを取得", 'records'=>$recordContents]);
        }
        $tgtRecord=$recordMenu->where(function($query) use($user_id, $category_id, $menu_id,$record_state_id){
            $query->where([['user_id', $user_id], ['category_id', $category_id], ['menu_id', $menu_id],['record_state_id', $record_state_id]]);
        })->first();

        if($tgtRecord){
            $tgtRecords=$tgtRecord->recordContents()->orderBy('set', 'asc')->get();
            return response()->json(["status_code" => 200, "message" => "記録日のデータを取得", 'tgtRecords'=>$tgtRecords]);
        }else{
            return response()->json(["status_code" => 200, "message" => "記録日のデータはありません", 'tgtRecords'=>$tgtRecords]);
        }
    }

    public function create(Request $request, RecordMenu $recordMenu,RecordContent $recordContent){
        $user_id = $request->user_id;
        $category_id = $request->category_id;
        $menu_id = $request->menu_id;
        $record_state_id = $request->record_state_id;
        
        $tgtRecordMenu=$recordMenu->where(function($query) use($user_id, $category_id, $menu_id,$record_state_id){
            $query->where([['user_id', $user_id], ['category_id', $category_id], ['menu_id', $menu_id],['record_state_id', $record_state_id]]);
        })->first();
        if($tgtRecordMenu){
            $tgtRecordContent = $tgtRecordMenu->recordContents()->where('set',$request->set)->first();
            if($tgtRecordContent){
                $tgtRecordContent->user_id=$request->user_id;
                $tgtRecordContent->record_state_id=$request->record_state_id;
                $tgtRecordContent->record_menu_id=$tgtRecordMenu->id;
                $tgtRecordContent->weight = $request->weight;
                $tgtRecordContent->right_weight = $request->right_weight;
                $tgtRecordContent->right_rep = $request->right_rep;
                $tgtRecordContent->left_weight = $request->left_weight;
                $tgtRecordContent->left_rep = $request->left_rep;
                $tgtRecordContent->set = $request->set;
                $tgtRecordContent->rep = $request->rep;
                $tgtRecordContent->memo = $request->memo;
                $tgtRecordContent->save();
                return response()->json(["status_code" => 200, "message" => "記録を更新しました。"]);
            }
        }else{
            // updateOrCreateを用いるにはインスタンス化が必要？
            // $recordMenu->updateOrCreate(
            //     ['user_id'=>$user_id],
            //     ['record_state_id'=>$record_state_id],
            //     ['menu_id'=>$menu_id],
            //     ['category_id'=>$category_id],
            // );
            $recordMenu->user_id=$request->user_id;
            $recordMenu->record_state_id=$record_state_id;
            $recordMenu->menu_id=$menu_id;
            $recordMenu->category_id=$category_id;
            $recordMenu->save();
        }
        $tgtRecordMenu=$recordMenu->where(function($query) use($user_id, $category_id, $menu_id,$record_state_id){
            $query->where([['user_id', $user_id], ['category_id', $category_id], ['menu_id', $menu_id],['record_state_id', $record_state_id]]);
        })->first();
        $recordContent->user_id=$request->user_id;
        $recordContent->record_state_id=$request->record_state_id;
        $recordContent->record_menu_id=$tgtRecordMenu->id;
        $recordContent->weight = $request->weight;
        $recordContent->right_weight = $request->right_weight;
        $recordContent->right_rep = $request->right_rep;
        $recordContent->left_weight = $request->left_weight;
        $recordContent->left_rep = $request->left_rep;
        $recordContent->set = $request->set;
        $recordContent->rep = $request->rep;
        $recordContent->memo = $request->memo;
        $recordContent->save();
        return response()->json(["status_code" => 200, "message" => "新規に記録しました。", "tgtRecordMenu"=>$tgtRecordMenu]);
    }

    public function delete(Request $request, RecordMenu $recordMenu,RecordContent $recordContent){
        $user_id = $request->user_id;
        $category_id = $request->category_id;
        $menu_id = $request->menu_id;
        $record_state_id = $request->record_state_id;

        $tgtRecordMenu=$recordMenu->where(function($query) use($user_id, $category_id, $menu_id,$record_state_id){
            $query->where([['user_id', $user_id], ['category_id', $category_id], ['menu_id', $menu_id],['record_state_id', $record_state_id]]);
        })->first();
        if($tgtRecordMenu){
            $tgtRecordContent = $tgtRecordMenu->recordContents()->where('set',$request->set)->first();
            if($tgtRecordContent){
                $tgtRecordContent->delete();
                $tgtRecordContent = $tgtRecordMenu->recordContents()->where('set',$request->set)->first();
            }
            if(!$tgtRecordContent){
                $tgtRecordMenu->delete();
            }
        }
        return response()->json(["status_code" => 200, "message" => "データを削除しました。"]);
    }
}

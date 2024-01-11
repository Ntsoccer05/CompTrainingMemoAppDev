import { ref } from "vue";
import axios from "axios";

export default function useGetTgtRecordContents(user_id, category_id, menu_id, record_state_id){
    const tgtRecord = ref("")
    // 既にデータが存在するか
    const hasTgtRecord = ref(false)

    //既にデータが存在していれば取得
    const getTgtRecords = async(user_id, category_id, menu_id, record_state_id)=>{
        await axios.get("/api/recordContent", {
            // get時にパラメータを渡す際はparamsで指定が必要
            params:{
                // keyとvalueが同じためuser_id:user_idの「:user_id」を省略できる
                user_id,
                category_id,
                menu_id,
                record_state_id,
            }
        }).then((res) =>{
            if(res.data.tgtRecords){
                tgtRecord.value = res.data.tgtRecords
                hasTgtRecord.value = true
            }else{
                hasTgtRecord.value = false
            }
        }).catch((err)=>{
            console.log(err)
        })
    }

    return{tgtRecord, hasTgtRecord, getTgtRecords}
}
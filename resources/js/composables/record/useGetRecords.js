import { ref } from "vue";
import axios from "axios";

export default function useGetRecords(){
    const records = ref("");
    const compGetData = ref(false);
    
    const getRecords = async(user_id, recorded_at=null)=>{
        await axios.get("/api/recordContent", {
            // get時にパラメータを渡す際はparamsで指定が必要
            params:{
                // keyとvalueが同じためuser_id:user_idの「:user_id」を省略できる
                user_id,
                recorded_at,
            }
        }).then((res) =>{
            records.value = res.data.records
            compGetData.value = true
        })
    }

    return{records, compGetData, getRecords}
}
import { useStore } from "vuex";
import { computed } from "vue";

export default function useSelected(day){
    const store = useStore();

    const selectedDay = ()=>{
        store.commit("selectedDay", day);
    }

    const recordingDay = computed(()=>{
        return store.getters.selectedDay;
    })

    if(typeof day === "string"){
        const date = new Date(day.replace(/-/g,"/"));
        // padStart(何桁表示するか, 文字埋めする文字)
        const postDay = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`;
        return {selectedDay, recordingDay, postDay};
    }else{
        const date = new Date(day);
        const postDay = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`;
        return {selectedDay, recordingDay, postDay};
    }
}

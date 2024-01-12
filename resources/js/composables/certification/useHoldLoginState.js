import { ref,nextTick } from "vue";
import { useStore } from "vuex";
import { useRouter } from 'vue-router'
import useNotLoginedRedirect from "./useNotLoginedRedirect";

export default function useHoldLoginState(){
    const store = useStore();
    const isLogined = ref(false);

    //ログイン状態保持するため
    const holdLoginState = async () => {
        // 非同期処理呼び出しのため async await
        await store.dispatch("loginState");
        await axios.get("/api/users")
          .then((res) => {
            // ログイン状態取得
            isLogined.value = true;
          })
          .catch((err) => {
            useNotLoginedRedirect(err)
          })
        // nextTickは非同期処理完了後に呼び出されるのでisLoginedを取得できる
        nextTick(()=>{
            isLogined.value = store.getters.isLogined;
        })
    };

    return {holdLoginState, isLogined};
}

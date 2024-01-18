import router from "../../router/index";

export default function useNotLoginedRedirect(err){
    if(err.response.data.message === "Unauthenticated." && router.currentRoute.value.meta.requiresAuth === true){
        router.push("/");
        alert("画面表示するにはログインしてください。");
    }
}

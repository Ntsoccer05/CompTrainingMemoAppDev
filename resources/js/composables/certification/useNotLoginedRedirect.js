import router from "../../router/index";

export default function useNotLoginedRedirect(err){
    if(err.response.data.message === "Unauthenticated." && window.location.pathname !== "/" && window.location.pathname !== "/login/google/callback"
    && window.location.pathname !== "/inquiry"){
        router.push("/");
        alert("画面表示するにはログインしてください。");
    }
}

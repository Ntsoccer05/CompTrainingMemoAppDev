<template>
  <form @submit.prevent="register">
    <!-- UserName input -->
    <div class="relative mb-6" data-te-input-wrapper-init>
      <input
        type="text"
        class="peer block min-h-[auto] w-full rounded border bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 data-[te-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-neutral-200 dark:placeholder:text-neutral-200 dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 [&:not([data-te-input-placeholder-active])]:placeholder:opacity-0"
        id="username"
        placeholder="ユーザ名"
        v-model="name"
      />
      <label
        for="username"
        class="pointer-events-none absolute duration-300 bg-white scale-[0.8] transform -translate-y-[1.15rem] top-2 origin-[0] text-neutral-500 px-2 peer-focus:px-2 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-[0.8] peer-focus:-translate-y-[1.15rem] left-1 dark:text-neutral-200 dark:peer-focus:text-primary"
        >ユーザ名<span class="text-sm text-black-600">(※任意)</span>
      </label>
      <p :class="dispNameErrMsg">{{ errors.name[0] }}</p>
    </div>

    <!-- Email input -->
    <div class="relative mb-6" data-te-input-wrapper-init>
      <input
        type="text"
        class="peer block min-h-[auto] w-full rounded border bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 data-[te-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-neutral-200 dark:placeholder:text-neutral-200 dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 [&:not([data-te-input-placeholder-active])]:placeholder:opacity-0"
        id="email"
        placeholder="メールアドレス"
        v-model="email"
        readonly
      />
      <label
        for="email"
        class="pointer-events-none absolute duration-300 bg-white scale-[0.8] transform -translate-y-[1.15rem] top-2 origin-[0] text-neutral-500 px-2 peer-focus:px-2 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-[0.8] peer-focus:-translate-y-[1.15rem] left-1 dark:text-neutral-200 dark:peer-focus:text-primary"
        >メールアドレス
      </label>
    </div>

    <!--Submit button-->
    <div class="mb-12 pb-1 pt-1 text-center">
      <button
        class="mb-3 inline-block w-full rounded px-6 pb-2 pt-2.5 text-xs font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_rgba(0,0,0,0.2)] transition duration-150 ease-in-out hover:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:outline-none focus:ring-0 active:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)]"
        type="submit"
        data-te-ripple-init
        data-te-ripple-color="light"
        style="background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)"
      >
        新規登録
      </button>
    </div>
  </form>
</template>

<script>
import { reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";
import useValidationMsg from "../../composables/certification/useValidationMsg";
import dispValidationMsg from "../../composables/certification/useDispValidationMsg";
export default {
  setup() {
    const route = useRoute();
    const router = useRouter();
    const store = useStore();

    const name = ref("");
    const email = route.query.email;
    const errors = reactive({
      name: [],
    });
    const dispErrorMsg = reactive({
      name: false,
    });

    //バリデーションエラーメッセージのレイアウト
    const { dispNameErrMsg } = dispValidationMsg(dispErrorMsg);

    const register = async () => {
      await axios
        // CSRF保護
        .get("/sanctum/csrf-cookie")
        .then(() => {
          axios
            .post("/api/register/google", {
              name: name.value,
              email,
              // GoogleのURIを用いて登録する場合tokenが必須
              token: route.query.token,
            })
            .then((res) => {
              if (res.data.status_code === 200) {
                router.push("/");
                store.dispatch("loginState");
              }
            })
            .catch((err) => {
              // POST時のバリデーションエラー
              const errorMsgs = err.response.data.errors;
              useValidationMsg(errorMsgs, errors, dispErrorMsg);
              console.log(err);
            });
        })
        .catch((err) => {
          console.log(err);
        });
    };

    return { name, email, errors, dispNameErrMsg, register };
  },
};
</script>

<style></style>
>

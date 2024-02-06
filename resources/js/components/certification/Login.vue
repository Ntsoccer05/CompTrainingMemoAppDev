<template>
  <form @submit.prevent="login">
    <!-- UserName input -->
    <div class="relative mb-6">
      <label for="name" class="block text-sm font-medium text-gray-700">ユーザ名 </label>
      <input
        type="text"
        class="mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="name"
        v-model="name"
        placeholder=""
      />
      <p :class="dispNameErrMsg">{{ errors.name[0] }}</p>
    </div>

    <!-- Email input -->
    <div class="relative mb-6">
      <label for="email" class="block text-sm font-medium text-gray-700"
        >メールアドレス
      </label>
      <input
        type="text"
        class="mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="email"
        v-model="email"
        placeholder="メールアドレス"
        required
      />
      <p :class="dispEmailErrMsg">{{ errors.email[0] }}</p>
    </div>

    <!-- Password input -->
    <div class="relative mb-6">
      <label for="password" class="block text-sm font-medium text-gray-700"
        >パスワード
      </label>
      <input
        :type="inputType"
        class="mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="password"
        v-model="password"
        placeholder="パスワード"
      />
      <label
        ><input type="checkbox" v-model="displayPass" class="mr-1 mt-2" />パスワードを表示する
      </label>
      <div class="absolute top-8.5 text-right w-11/12 pointer-events-none">
        <span class="pointer-events-auto"
          ><i :class="iconType" @click="toggleDisplayPass"></i
        ></span>
      </div>
      <p :class="dispPassErrMsg">{{ errors.password }}</p>
    </div>

    <!--Submit button-->
    <div class="mb-6 md:mb-0 pb-1 pt-1 text-center">
      <button
        class="mb-3 inline-block w-full rounded px-6 pb-2 pt-2.5 text-base font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_rgba(0,0,0,0.2)] transition duration-150 ease-in-out hover:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:outline-none focus:ring-0 active:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)]"
        type="submit"
        data-te-ripple-init
        data-te-ripple-color="light"
        style="background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)"
      >
        ログイン
      </button>
    </div>
  </form>
</template>

<script>
import { ref, reactive, computed } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import useValidationMsg from "../../composables/certification/useValidationMsg";
import dispValidationMsg from "../../composables/certification/useDispValidationMsg";
export default {
  setup() {
    const router = useRouter();
    const store = useStore();
    const name = ref("");
    const email = ref("");
    const password = ref("");
    const getUserMessage = ref("");
    const errors = reactive({
      name: [],
      email: [],
      password: [],
    });
    const dispErrorMsg = reactive({
      name: false,
      email: false,
      password: false,
    });
    const displayPass = ref(false);

    // パスワードの表示／非表示
    const inputType = computed(() => {
      return displayPass.value ? "text" : "password";
    });
    const iconType = computed(() => {
      return displayPass.value ? "fa-solid fa-eye-slash" : "fa-solid fa-eye";
    });

    //バリデーションエラーメッセージのレイアウト
    const { dispNameErrMsg, dispEmailErrMsg, dispPassErrMsg } = dispValidationMsg(
      dispErrorMsg
    );

    // ログイン処理
    const login = async () => {
      await axios
        // CSRF保護
        .get("/sanctum/csrf-cookie")
        .then((res) => {
          axios
            .post("/api/login", {
              // バリデーションをLoginRequest.phpで行っているため、オブジェクト名を合わせる必要がある。
              name: name.value,
              email: email.value,
              password: password.value,
            })
            .then((res) => {
              if (res.data.status_code == "200") {
                router.push("/");
                // ログイン状態を変更するためVuexより呼び出し
                store.dispatch("loginState");
              }
              getUserMessage.value = "ログインに失敗しました。";
            })
            .catch((err) => {
              // POST時のバリデーションエラー
              const errorMsgs = err.response.data.errors;
              useValidationMsg(errorMsgs, errors, dispErrorMsg);

              getUserMessage.value = "ログインに失敗しました。";
            });
        })
        .catch((err) => {
          getUserMessage.value = "ログインに失敗しました。";
        });
    };
    // 学習のため後にfetchを使って実装

    const toggleDisplayPass = () => {
      displayPass.value = !displayPass.value;
    };

    return {
      name,
      email,
      password,
      errors,
      inputType,
      iconType,
      displayPass,
      dispNameErrMsg,
      dispEmailErrMsg,
      dispPassErrMsg,
      login,
      toggleDisplayPass,
    };
  },
};
</script>

<style></style>

<script setup>
import { reactive, ref, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import useValidationMsg from "../../composables/certification/useValidationMsg";
import dispValidationMsg from "../../composables/certification/useDispValidationMsg";

const router = useRouter();

//パスワードリセットURLのGETパラメータよりトークンとメールアドレスを抽出し変数に保存
const queryParameters = new URLSearchParams(window.location.search);
const email = queryParameters.get("email");
const token = queryParameters.get("token");

const dispAlertModal = ref(false);

// 送信データ
const form = reactive({
  email,
  token,
  password: "",
  password_confirmation: "",
});

// エラーメッセージ格納
const errors = reactive({
  email: [],
  password: [],
});
// エラーメッセージを表示するか
const dispErrorMsg = reactive({
  email: false,
  password: false,
});

const toLogin = () => {
  router.push("/login");
};

// パスワードと確認用パスワードのタイプをパスワードかテキストか
const displayPass = ref(false);
const displayConfirmPass = ref(false);

// パスワードの表示／非表示
const inputType = computed(() => {
  return displayPass.value ? "text" : "password";
});
const inputConfirmType = computed(() => {
  return displayConfirmPass.value ? "text" : "password";
});
const iconType = computed(() => {
  return displayPass.value ? "fa-solid fa-eye-slash" : "fa-solid fa-eye";
});
const iconConfirmType = computed(() => {
  return displayConfirmPass.value ? "fa-solid fa-eye-slash" : "fa-solid fa-eye";
});
const toggleDisplayPass = () => {
  displayPass.value = !displayPass.value;
};
const toggleConfirmlayPass = () => {
  displayConfirmPass.value = !displayConfirmPass.value;
};

//バリデーションエラーメッセージのレイアウト
const { dispEmailErrMsg, dispPassErrMsg } = dispValidationMsg(dispErrorMsg);

// パスワード変更ボタン押下処理
const sendForgotPasswordEmail = async () => {
  await axios.get("sanctum/csrf-cookie");
  await axios
    .post("/api/password/reset", {
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      token: form.token,
    })
    .then((res) => {
      // 成功時
      dispAlertModal.value = true;
    })
    .catch((err) => {
      // POST時のバリデーションエラー
      const errorMsgs = err.response.data.errors;
      useValidationMsg(errorMsgs, errors, dispErrorMsg);
    });
};
</script>

<template>
  <form @submit.prevent="sendForgotPasswordEmail" class="w-11/12 mx-auto">
    <!-- Email input -->
    <div class="relative mb-6 mt-10" data-te-input-wrapper-init>
      <label for="email" class="block text-sm font-medium text-gray-700"
        >メールアドレス
      </label>
      <input
        type="text"
        class="mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="email"
        v-model="form.email"
        placeholder="メールアドレス"
      />
      <p :class="dispEmailErrMsg">{{ errors.email[0] }}</p>
    </div>

    <!-- Password input -->
    <div class="relative mb-6" data-te-input-wrapper-init>
      <label for="password" class="block text-sm font-medium text-gray-700"
        >パスワード
      </label>
      <input
        :type="inputType"
        class="mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="password"
        v-model="form.password"
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
      <p :class="dispPassErrMsg">{{ errors.password[0] }}</p>
    </div>

    <!-- Password input -->
    <div class="relative mb-6" data-te-input-wrapper-init>
      <label for="password_confirmation" class="block text-sm font-medium text-gray-700"
        >確認用パスワード
      </label>
      <input
        :type="inputConfirmType"
        class="mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="password_confirmation"
        v-model="form.password_confirmation"
        placeholder="確認用パスワード"
      />
      <label
        ><input
          type="checkbox"
          v-model="displayConfirmPass"
          class="mr-1 mt-2"
        />パスワードを表示する
      </label>
      <div class="absolute top-3 text-right w-11/12 pointer-events-none">
        <span class="pointer-events-auto"
          ><i :class="iconConfirmType" @click="toggleConfirmlayPass"></i
        ></span>
      </div>
      <p :class="dispPassErrMsg">{{ errors.password[0] }}</p>
    </div>

    <!--Submit button-->
    <div class="mb-6 md:mb-0 pb-1 pt-1 text-center">
      <button
        class="mb-3 inline-block w-full rounded px-6 pb-2 pt-2.5 text-lg font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_rgba(0,0,0,0.2)] transition duration-150 ease-in-out hover:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:outline-none focus:ring-0 active:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)]"
        type="submit"
        data-te-ripple-init
        data-te-ripple-color="light"
        style="background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)"
      >
        パスワードを変更する
      </button>
    </div>
  </form>
  <Modal
    v-model="dispAlertModal"
    title="パスワード変更完了"
    wrapper-class="modal-wrapper"
    class="flex align-center"
    @closing="toLogin()"
  >
    <p>パスワードを変更しました。ログインお願いします。</p>
    <button
      class="col-12 mt-5 text-center inline-block w-full rounded px-6 pb-2 pt-2.5 text-base font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_rgba(0,0,0,0.2)] transition duration-150 ease-in-out hover:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:outline-none focus:ring-0 active:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)]"
      style="background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)"
      @click="toLogin"
    >
      ログイン画面へ
    </button>
  </Modal>
</template>

<style scoped></style>

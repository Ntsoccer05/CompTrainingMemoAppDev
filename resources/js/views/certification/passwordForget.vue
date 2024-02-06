<script setup>
import { reactive, ref } from "vue";
import axios from "axios";
import useValidationMsg from "../../composables/certification/useValidationMsg";
import dispValidationMsg from "../../composables/certification/useDispValidationMsg";

// 送信データ
const form = reactive({
  email: "",
});

// エラーメッセージ用
const errors = reactive({
  email: [],
});
const dispErrorMsg = reactive({
  email: false,
});

const dispAlertModal = ref(false);

//バリデーションエラーメッセージのレイアウト
const { dispEmailErrMsg } = dispValidationMsg(dispErrorMsg);

const sendForgotPasswordEmail = async () => {
  await axios.get("sanctum/csrf-cookie");
  await axios
    .post("/api/password/forget", {
      email: form.email,
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

    <!--Submit button-->
    <div class="mb-6 md:mb-0 pb-1 pt-1 text-center">
      <button
        class="mb-3 inline-block w-full rounded px-6 pb-2 pt-2.5 text-lg font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_rgba(0,0,0,0.2)] transition duration-150 ease-in-out hover:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:outline-none focus:ring-0 active:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)]"
        type="submit"
        data-te-ripple-init
        data-te-ripple-color="light"
        style="background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)"
      >
        パスワード変更メールを送る
      </button>
    </div>
  </form>
  <Modal
    v-model="dispAlertModal"
    title="パスワード変更メール送信完了"
    wrapper-class="modal-wrapper"
    class="flex align-center"
  >
    <p>メールを送信しました。確認してください。</p>
  </Modal>
</template>

<style scoped></style>

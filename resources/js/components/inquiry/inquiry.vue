<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import useValidationMsg from "../../composables/inquiry/useValidationMsg";
import dispValidationMsg from "../../composables/inquiry/useDispValidationMsg";

const router = useRouter();

// 送信データ
const form = reactive({
  name: "",
  email: "",
  content: "",
});

// エラーメッセージ格納
const errors = reactive({
  email: [],
  content: [],
});
// エラーメッセージを表示するか
const dispErrorMsg = reactive({
  email: false,
  content: false,
});

const dispAlertModal = ref(false);
const btnEnabled = ref(false);
const btnColor = ref("background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)");

//バリデーションエラーメッセージのレイアウト
const { dispEmailErrMsg, dispContentErrMsg } = dispValidationMsg(dispErrorMsg);

// 送信するボタン押下処理
const sendEmail = async () => {
  btnEnabled.value = true;
  btnColor.value = "background: linear-gradient(to right, rgb(238 119 36 / 30%), rgb(216 54 58 / 30%), rgb(221 54 117 / 30%), rgb(180 69 147 / 30%))";
  await axios
    .post("/api/inquiry", {
      name: form.name,
      email: form.email,
      content: form.content,
    })
    .then((res) => {
      // 成功時
      dispAlertModal.value = true;
      btnEnabled.value = false;
      btnColor.value = "background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)";
      //エラーメッセージを非表示
      //バリデーションメッセージを初期化
      dispErrorMsg.email = false;
      dispErrorMsg.content = false;
      //バリデーションエラーメッセージのレイアウト
      const { dispEmailErrMsg, dispContentErrMsg } = dispValidationMsg(dispErrorMsg);
    })
    .catch((err) => {
      btnEnabled.value = false;
      btnColor.value = "background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)";
      // POST時のバリデーションエラー
      const errorMsgs = err.response.data.errors;
      useValidationMsg(errorMsgs, errors, dispErrorMsg);
    });
};

// エンターキーを押すと次の要素入力可
function keydown(e) {
  if (e.keyCode === 13) {
    var obj = document.activeElement;
    if (obj.nextElementSibling && obj.parentNode.nextElementSibling) {
      if (
        obj.parentNode.nextElementSibling.children &&
        obj.parentNode.nextElementSibling.children[0]
      ) {
        if (obj.parentNode.nextElementSibling.children[0].nodeName == "INPUT") {
          obj.parentNode.nextElementSibling.children[0].focus();
        }
      } else if (
        obj.parentNode.nextElementSibling.nextElementSibling &&
        obj.parentNode.nextElementSibling.nextElementSibling.children &&
        obj.parentNode.nextElementSibling.nextElementSibling.children[0]
      ) {
        if (
          obj.parentNode.nextElementSibling.nextElementSibling.children[0].nodeName ==
          "TEXTAREA"
        ) {
          obj.parentNode.nextElementSibling.nextElementSibling.children[0].focus();
          // 1行目指定のため(無いと2行目指定となる)
          e.returnValue = false;
        }
      }
    }
  }
}

window.onkeydown = keydown;
</script>

<template>
  <form id="form" @submit.prevent="sendEmail" class="h-full w-full">
    <!-- Name input -->
    <div class="relative mb-6" data-te-input-wrapper-init>
      <label for="name" class="block text-sm font-medium text-gray-700">お名前 </label>
      <input
        type="text"
        class="mt-1 p-2 w-full border rounded-md border-gray-300 focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="name"
        v-model="form.name"
        placeholder="お名前"
      />
    </div>

    <!-- Email input -->
    <div class="relative mb-6" data-te-input-wrapper-init>
      <label for="email" class="block text-sm font-medium text-gray-700"
        >メールアドレス<span class="text-sm text-red-600">(※必須)</span>
      </label>
      <input
        type="text"
        class="mt-1 p-2 w-full border rounded-md border-gray-300 focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="email"
        v-model="form.email"
        placeholder="メールアドレス"
      />
      <p :class="dispEmailErrMsg">{{ errors.email[0] }}</p>
    </div>

    <!-- content input -->
    <div class="relative h-2/3 mb-6" data-te-input-wrapper-init>
      <label for="content" class="block text-sm font-medium text-gray-700"
        >お問い合わせ内容<span class="text-sm text-red-600">(※必須)</span>
      </label>
      <textarea
        class="mt-1 p-2 w-full h-full border rounded-md border-gray-300 focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="content"
        v-model="form.content"
        placeholder="追加してほしい機能や相談ごとなど承ります"
      />
      <p :class="dispContentErrMsg">{{ errors.content[0] }}</p>
    </div>

    <!--Submit button-->
    <div class="mb-6 mt-10 md:mb-0 pb-1 pt-1 text-center">
      <button
        class="mb-3 inline-block w-full rounded px-6 pb-2 pt-2.5 text-base font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_rgba(0,0,0,0.2)] transition duration-150 ease-in-out hover:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:outline-none focus:ring-0 active:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)]"
        type="button"
        data-te-ripple-init
        data-te-ripple-color="light"
        :style="btnColor"
        @click.prevent="sendEmail"
        :disabled="btnEnabled"
      >
        送信する
      </button>
    </div>
  </form>
  <Modal v-model="dispAlertModal" title="お問い合わせ送信完了" wrapper-class="modal-wrapper" class="flex align-center"
  >
    <p>お問い合わせ内容を送信しました。</p>
  </Modal>
</template>

<style scoped></style>

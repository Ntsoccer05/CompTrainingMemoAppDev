<template>
  <form @submit.prevent="register">
    <!-- UserName input -->
    <div class="relative mb-6" data-te-input-wrapper-init>
      <label for="username" class="block text-sm font-medium text-gray-700"
        >ユーザ名<span class="text-sm text-black-600">(※任意)</span>
      </label>
      <input
        type="text"
        class="mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="username"
        placeholder="ユーザ名"
        v-model="name"
      />
      <p :class="dispNameErrMsg">{{ errors.name[0] }}</p>
    </div>

    <!-- Email input -->
    <div class="relative mb-6" data-te-input-wrapper-init>
      <label for="email" class="block text-sm font-medium text-gray-700"
        >メールアドレス
      </label>
      <input
        type="text"
        class="mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="email"
        placeholder="メールアドレス"
        v-model="email"
        required
      />
      <p :class="dispEmailErrMsg">{{ errors.email[0] }}</p>
    </div>

    <!-- Password input -->
    <div class="relative mb-6" data-te-input-wrapper-init>
      <label class="block text-sm font-medium text-gray-700" for="password"
        >パスワード
      </label>
      <input
        :type="inputType"
        class="mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300"
        id="password"
        placeholder="パスワード"
        v-model="password"
        required
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
    <div class="mb-12 pb-1 pt-1 text-center">
      <button
        class="mb-3 inline-block w-full rounded px-6 pb-2 pt-2.5 text-base font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_rgba(0,0,0,0.2)] transition duration-150 ease-in-out hover:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:outline-none focus:ring-0 active:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)]"
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
import { ref, reactive, computed } from "vue";
import { useRouter } from "vue-router";
import useValidationMsg from "../../composables/certification/useValidationMsg.js";
import dispValidationMsg from "../../composables/certification/useDispValidationMsg";
export default {
  setup() {
    const router = useRouter();
    const name = ref("");
    const email = ref("");
    const password = ref("");
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

    const register = async () => {
      await axios
        // CSRF保護
        .get("/sanctum/csrf-cookie")
        .then((res) => {
          axios
            .post("/api/register", {
              name: name.value,
              email: email.value,
              password: password.value,
            })
            .then((res) => {
              if (res.data.status_code === 200) {
                router.push("/login");
              }
            })
            .catch((err) => {
              // POST時のバリデーションエラー
              const errorMsgs = err.response.data.errors;
              useValidationMsg(errorMsgs, errors, dispErrorMsg);
            });
        })
        .catch((err) => {
          console.log(err);
        });
    };

    const toggleDisplayPass = () => {
      displayPass.value = !displayPass.value;
    };

    return {
      name,
      email,
      password,
      inputType,
      iconType,
      errors,
      displayPass,
      dispNameErrMsg,
      dispEmailErrMsg,
      dispPassErrMsg,
      register,
      toggleDisplayPass,
    };
  },
};
</script>

<style></style>
>

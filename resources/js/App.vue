<template>
  <div class="m-0 p-0 h-screen">
    <Header />
    <router-view />
  </div>
</template>

<script>
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import Header from "./components/headerMenu/Header.vue";
import useHoldLoginState from "./composables/certification/useHoldLoginState";

export default {
  components: {
    Header,
  },
  setup() {
    const router = useRouter();
    //ログイン状態をリロードしても維持するため
    const { holdLoginState } = useHoldLoginState();

    // async await を使わないとユーザ情報取得する前にMountedサイクルが終了してしまう
    onMounted(async () => {
      await holdLoginState();
    });
  },
};
</script>

<style>
/* autocomplete時に入力エリアの色が変わるのを阻止 */
input:-webkit-autofill,
textarea:-webkit-autofill,
select:-webkit-autofill {
  box-shadow: 0 0 0 1000px white inset !important;
  -webkit-text-fill-color: black !important;
}
input[type="text"],
textarea {
  -webkit-appearance: none;
}
</style>

// 以下で指定しているstyleはModalのため動的に追加される要素であるためdeepを指定
<style deep>
.vm-wrapper {
  display: flex;
  align-items: center;
}
.vm-wrapper .vm {
  top: auto;
}
</style>

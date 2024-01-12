<template>
  <form @submit.prevent="record">
    <button
      type="submit"
      class="block w-11/12 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 border-2 border-black mb-8 mx-auto"
      @click="alertLogin"
    >
      今日のトレーニングを記録する
    </button>
  </form>
</template>

<script>
import { onMounted, ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import useGetLoginUser from "../../composables/certification/useGetLoginUser.js";
import useSelectedDay from "../../composables/record/useSelectedDay";
export default {
  setup() {
    const date = new Date();
    const router = useRouter();
    const store = useStore();
    const isLogined = ref(false);

    const { getLoginUser, loginUser } = useGetLoginUser();
    onMounted(async () => {
      await getLoginUser();
      // ログイン状態をVuexより取得<-このタイミングだとカレンダーの描画が完了しているためVuexの値を取得できる。
      isLogined.value = computed(() => store.state.isLogined);
    });

    //ログインしていなかったらメッセージを表示
    const alertLogin = () => {
      if (isLogined.value.value === false) {
        alert("ログインしてください");
      }
    };

    const { selectedDay, recordingDay, postDay } = useSelectedDay(date);

    selectedDay();

    const record = async () => {
      if (isLogined.value.value) {
        await axios
          .post("/api/record/create", {
            user_id: loginUser.value.id,
            recording_day: postDay,
          })
          .then((res) => {
            store.commit("setRecordedAt", postDay);
            router.push({ name: "selectMenu", params: { recordId: postDay } });
          })
          .catch((err) => {
            console.log(err);
          });
      }
    };

    const toSelectMenu = () => {
      // トレーニング記録画面へ遷移
      store.commit("setRecordedAt", postDay);
      router.push({ name: "selectMenu", params: { recordId: postDay } });
    };
    return { recordingDay, loginUser, toSelectMenu, record, alertLogin };
  },
};
</script>

<style></style>

<template>
  <p class="text-red-500 sm:text-center font-bold text-lg mb-5" v-if="loginUser.id">
      ※記録なし、セット数０のデータは毎朝４時に削除されます
  </p>
  <template v-if="isLoaded && compGetData">
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
  <Modal
    v-model="dispAlertModal"
    title="権限エラー"
    wrapper-class="modal-wrapper"
    class="flex align-center"
  >
    <p>ログインしてください。</p>
    <button
      class="col-12 mt-5 text-center inline-block w-full rounded px-6 pb-2 pt-2.5 text-base font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_rgba(0,0,0,0.2)] transition duration-150 ease-in-out hover:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:outline-none focus:ring-0 active:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)]"
      style="background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)"
      @click="toLogin"
    >
      ログイン画面へ
    </button>
  </Modal>
</template>

<script>
import { onMounted, ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import useGetLoginUser from "../../composables/certification/useGetLoginUser.js";
import useSelectedDay from "../../composables/record/useSelectedDay";
import useGetRecords from "../../composables/record/useGetRecords";
export default {
  props: {
    compGetData: Boolean,
  },
  setup(props) {
    const date = new Date();
    const router = useRouter();
    const store = useStore();

    const compGetData = computed(() => props.compGetData);

    const isLogined = ref(false);
    const dispAlertModal = ref(false);

    const { getLoginUser, loginUser } = useGetLoginUser();
    onMounted(async () => {
      await getLoginUser();
      // ログイン状態をVuexより取得<-このタイミングだとカレンダーの描画が完了しているためVuexの値を取得できる。
      isLogined.value = computed(() => store.state.isLogined);
      if (loginUser.value.id) {
        await getRecords(loginUser.value.id);
      } else {
        await getRecords(0);
      }
    });

    const toLogin = () => {
      router.push("/login");
    };

    //ログインしていなかったらメッセージを表示
    const alertLogin = () => {
      if (isLogined.value.value === false) {
       // alert("ログインしてください");
       dispAlertModal.value = true;
      }
    };

    const { selectedDay, recordingDay, postDay } = useSelectedDay(date);

    const { isLoaded, getRecords } = useGetRecords();

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
    return {
      recordingDay,
      compGetData,
      dispAlertModal,
      loginUser,
      isLogined,
      isLoaded,
      toSelectMenu,
      record,
      alertLogin,
      toLogin,
    };
  },
};
</script>

<style></style>

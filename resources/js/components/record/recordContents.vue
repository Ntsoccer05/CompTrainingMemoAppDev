<template>
  <div>
   <template v-if="compGetData">
    <table class="border border-collapse table-fixed mx-auto">
      <caption
        class="p-5 text-lg font-semibold text-left text-gray-900 bg-white dark:text-white dark:bg-gray-800"
      >
        <button
          class="block w-11/12 bg-blue-500 hover:bg-blue-700 text-white font-bold md:py-2 py-px px-4 border-2 border-black mt-3 mb-3 mx-auto"
          ref="fillBeforeBtn"
	  @click="fillBeforeRecord"
        >
          {{ BeforeBtnTxt }}
        </button>
        <button
            class="block w-11/12 bg-green-500 hover:bg-green-700 text-white font-bold md:py-2 py-px px-4 border-2 border-black mt-3 mb-3 mx-auto"
            @click="confirmHistory()"
          >
            履歴を確認
        </button>
	  <p
            :class="[
              'mx-auto text-red-500 text-sm mt-1 mb-2 text-center',
              isDispTxt ? 'block' : 'hidden',
            ]"
          >
            ※前回の記録を埋めるためには今回の記録を埋めてください
          </p>
	  <div class="text-center mt-5">
            <input
              class="bg-slate-100 border-black border-x border-y mr-2"
              id="complementContents"
              type="checkbox"
              v-model="complementContents"
            />
            <label for="complementContents" class="text-base align-[1px]">重量・回数を補完する</label>
          </div>
        <div class="grid grid-cols-2 w-full">
          <div>
            <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">
              今回の体重：{{ bodyWeight }}
            </p>
            <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">
              今回の合計セット数：{{ thisTotalSet }}
            </p>
          </div>
          <template v-if="isBeforeData">
            <div>
              <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">
                {{ BeforeWeightTxt }}：{{ beforeBodyWeight }}
              </p>
              <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">
                {{ BeforeTotalSetTxt }}：{{ beforeTotalSet }}
              </p>
            </div>
          </template>
          <template v-else>
            <div>
              <p>{{ msgNoBeforeData }}</p>
            </div>
          </template>
        </div>
      </caption>
      <RecordTable
        :second_record="secondRecord"
        :hasSecondRecord="hasSecondRecord"
        :hasOneHand="hasOneHand"
        :category_id="category_id"
        :menu_id="menu_id"
        :record_state_id="record_state_id"
	:menu_content="menuContent"
	:beforeHeaderTxt="BeforeHeaderTxt"
        :complementContents="complementContents"
        @beforeTotalSet="fillBeforeTodalSet"
        @totalSet="fillThisTodalSet"
        @canClick="ableToClickBefore"
      />
    </table>
    </template>
    <template v-else>
      <p class="mx-auto mt-10 md:w-6/12 w-11/12 mb-5 font-bold text-center">
        データ取得中です。しばらくお待ちください。
      </p>
    </template>
    <Modal v-model="showModal" :title="menuContent" modal-class="scrollable-modal">
      <div class="scrollable-content">
        <HistoryRecordContents
          :historyMenus="historyMenus"
          :historyRecords="historyRecords"
          :hasHistoryRecord="hasHistoryRecord"
          :hasOneHand="hasOneHand"
        />
      </div>
      <div class="row scrollable-modal-footer">
        <div class="col-sm-12">
          <div class="text-center">
            <button
              class="block w-11/12 bg-blue-500 hover:bg-blue-700 text-white font-bold border-2 border-black mx-auto"
              type="button"
              @click="showModal = false"
            >
              閉じる
            </button>
          </div>
        </div>
      </div>
    </Modal>
    <Modal
      v-model="dispAlertModal"
      title="権限エラー"
      wrapper-class="modal-wrapper"
      class="flex align-center"
      @closing="toHome()"
    >
      <p>画面表示するにはログインしてください。</p>
      <button
        class="col-12 mt-5 text-center inline-block w-full rounded px-6 pb-2 pt-2.5 text-base font-medium uppercase leading-normal text-white shadow-[0_4px_9px_-4px_rgba(0,0,0,0.2)] transition duration-150 ease-in-out hover:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)] focus:outline-none focus:ring-0 active:shadow-[0_8px_9px_-4px_rgba(0,0,0,0.1),0_4px_18px_0_rgba(0,0,0,0.2)]"
        style="background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593)"
        @click="toLogin"
      >
        ログイン画面へ
      </button>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter, onBeforeRouteLeave } from "vue-router";
import { useStore } from "vuex";
import useGetRecordState from "../../composables/record/useGetRecordState";
import useGetLoginUser from "../../composables/certification/useGetLoginUser.js";
import useGetSecondRecordContent from "../../composables/record/useGetSecondRecordContent.js";
import RecordTable from "./RecordTable.vue";
import useGetTgtRecordContent from "../../composables/record/useGetTgtRecordContent.js";
import HistoryRecordContents from "./HistoryRecordContents.vue";
import useGetHistoryRecordContent from "../../composables/record/useGetHistoryRecordContent.js";
    const route = useRoute();
    const store = useStore();
    const router = useRouter();

    const hasOneHand = ref(false);

    const bodyWeight = ref("");
    const beforeBodyWeight = ref("");

    const category_id = route.query.categoryId;
    const menu_id = route.query.menuId;
    const record_state_id = route.query.recordId;

    const thisTotalSet = ref("");
    const beforeTotalSet = ref("");

    const msgNoBeforeData = ref("");

    const fillBeforeBtn = ref("");
    const compGetData = ref(false);

    // 履歴を表示するか
    const showModal = ref(false);

　　// 前回か前々回か
    const BeforeBtnTxt = ref("");
    const isDispTxt = ref(false);
    const BeforeWeightTxt = ref("");
    const BeforeTotalSetTxt = ref("");
    const BeforeHeaderTxt = ref("");

    const menuContent = ref("");

    const dispModal = computed(() => store.getters.dispAlertModal);
    const dispAlertModal = ref(false);

    // 自動補完するか
　　const complementContents = ref(false);

    //前回データが存在するか？
    const isBeforeData = ref(false);

    // メニューはセレクトボックス、休憩時間はタイムピッカー
    const header = {
      set: "セット数",
      menu: "メニュー",
      weight: "重量(kg)",
      rep: "回数",
      rest: "休憩時間",
    };

    // 最新のレコード状態を取得
    const { getLatestRecordState, latestRecord } = useGetRecordState();

    //今回記録するデータの値を取得
    const { hasTgtRecord, getTgtRecords } = useGetTgtRecordContent();

    const { getLoginUser, loginUser } = useGetLoginUser();

    //前回のデータを取得
    const {
      secondRecord,
      secondRecordState,
      hasSecondRecord,
      getSecondRecord,
    } = useGetSecondRecordContent();

    const toHome = () => {
      //router.pushが効かない
      window.location.href = "/";
    };
    const toLogin = () => {
      router.push("/login");
    };

    // 片方ずつ記録するかどうかmenusテーブルのoneSideカラムにて判断
    const getMenuContent = async () => {
      await axios
        .get("/api/menus", {
          params: {
            user_id: loginUser.value.id,
            category_id: category_id,
            menu_id: menu_id,
          },
        })
        .then((res) => {
	  menuContent.value = res.data.menu.content;
          if (res.data.menu.oneSide === 1) {
            hasOneHand.value = true;
          } else {
            hasOneHand.value = false;
          }
        })
        .catch((err) => [console.log(err)]);
    };

    const fillBeforeRecord = async () => {
      await getSecondRecord(
        loginUser.value.id,
        category_id,
        menu_id,
        record_state_id,
        route.params.recordId,
        thisTotalSet.value
      );
      if (hasSecondRecord.value) {
        isBeforeData.value = true;
        beforeBodyWeight.value = secondRecordState.value.bodyWeight;
      } else {
        msgNoBeforeData.value = "記録がありません";
      }
　　　if (BeforeBtnTxt.value === "前回の記録を埋める") {
        BeforeWeightTxt.value = "前回の体重";
        BeforeTotalSetTxt.value = "前回の合計セット数";
        BeforeHeaderTxt.value = "前回の記録";
      } else {
        // BeforeWeightTxt.value = "前々回の体重";
        // BeforeTotalSetTxt.value = "前々回の合計セット数";
        // BeforeHeaderTxt.value = "前々回の記録";
      }
    };

    //第一引数に子供の値が入っている。
    const fillThisTodalSet = (e) => {
      thisTotalSet.value = e;
    };

    const fillBeforeTodalSet = (e) => {
      beforeTotalSet.value = e;
    };

    const ableToClickBefore = (e) => {
      if (e) {
        BeforeBtnTxt.value = "前回の記録を埋める";
        isDispTxt.value = false;
      } else {
        // BeforeBtnTxt.value = "前々回の記録を埋める";
        // isDispTxt.value = true;
      }
    };

    const {
  historyRecords,
  historyMenus,
  hasHistoryRecord,
  getHistoryRecords,
} = useGetHistoryRecordContent();

const confirmHistory = async () => {
  //今回記録するデータの値を取得
  await getHistoryRecords(
    loginUser.value.id,
    category_id,
    menu_id,
    record_state_id,
    route.params.recordId
  );
  showModal.value = true;
};

const deleteFirstRecord = async () => {
  await axios
    .post("/api/recordContent/delete", {
      user_id: loginUser.value.id,
      category_id: route.query.categoryId,
      menu_id: route.query.menuId,
      record_state_id: route.query.recordId,
      recorded_at: route.params.recordId,
      set: 0,
    })
    .then((res) => {
    })
    .catch((err) => {
    });
};

const firstRecord = async () => {
  await axios
    .post("/api/recordContent/create", {
      user_id: loginUser.value.id,
      category_id: route.query.categoryId,
      menu_id: route.query.menuId,
      record_state_id: route.query.recordId,
      recorded_at: route.params.recordId,
      set: 0,
    })
    .then((res) => {})
    .catch((err) => {});
};

    onMounted(async () => {
      await getLoginUser();
      if (dispModal.value) {
        dispAlertModal.value = true;
      }
      // await firstRecord();
      await getLatestRecordState();
      await getMenuContent();
      await getTgtRecords(loginUser.value.id, category_id, menu_id, record_state_id);
      //if (hasTgtRecord.value) {
        BeforeBtnTxt.value = "前回の記録を埋める";
	BeforeWeightTxt.value = "前回の体重";
        BeforeTotalSetTxt.value = "前回の合計セット数";
        BeforeHeaderTxt.value = "前回の記録";
        isDispTxt.value = false;
        compGetData.value = true;
      //} else {
        // BeforeBtnTxt.value = "前々回の記録を埋める";
        // BeforeWeightTxt.value = "前々回の体重";
        // BeforeTotalSetTxt.value = "前々回の合計セット数";
        // BeforeHeaderTxt.value = "前々回の記録";
        // isDispTxt.value = true;
        // compGetData.value = true;
      //}
      const fillBeforeBtnDom = fillBeforeBtn.value;

      if (latestRecord.value.bodyWeight) {
        bodyWeight.value = `${latestRecord.value.bodyWeight} kg`;
      } else {
        // bodyWeight.value = "記録されていません";
      }
    });

    //遷移前処理
//onBeforeRouteLeave(async (to, from, next) => {
  //if (to.name === "selectMenu") {
    //if (thisTotalSet.value === 0) {
      //await deleteFirstRecord();
      //next();
    //} else {
      //next();
    //}
  //} else {
    //next();
  //}
//});

</script>

// vue-modalのレイアウト作成
<style deep>
.scrollable-modal {
  display: flex;
  flex-direction: column;
  height: calc(100% - 50px);
}
.scrollable-modal .vm-titlebar {
  flex-shrink: 0;
}
.scrollable-modal .vm-content {
  padding: 0;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
}
.scrollable-modal .vm-content .scrollable-content {
  position: relative;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 10px 15px 10px 15px;
  flex-grow: 1;
}
.scrollable-modal .scrollable-modal-footer {
  padding: 15px 0px 15px 0px;
  border-top: 1px solid #e5e5e5;
  margin-left: 0;
  margin-right: 0;
}
.vm-titlebar {
  text-align: center;
}
</style>

<template>
  <div>
    <table class="border border-collapse table-fixed mx-auto">
      <caption
        class="p-5 text-lg font-semibold text-left text-gray-900 bg-white dark:text-white dark:bg-gray-800"
      >
        <button
          class="block w-11/12 bg-blue-500 hover:bg-blue-700 text-white font-bold md:py-2 py-px px-4 border-2 border-black mt-3 mb-3 mx-auto"
          @click="fillBeforeRecord"
        >
          前回の記録を埋める
        </button>
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
                前回の体重：{{ beforeBodyWeight }}
              </p>
              <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">
                前回の合計セット数：{{ beforeTotalSet }}
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
        @beforeTotalSet="fillBeforeTodalSet"
        @totalSet="fillThisTodalSet"
      />
    </table>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import useGetRecordState from "../../composables/record/useGetRecordState";
import useGetLoginUser from "../../composables/certification/useGetLoginUser.js";
import useGetSecondRecordContent from "../../composables/record/useGetSecondRecordContent";
import RecordTable from "./RecordTable.vue";
export default {
  components: {
    RecordTable,
  },
  setup() {
    const route = useRoute();
    const hasOneHand = ref(false);

    const bodyWeight = ref("");
    const beforeBodyWeight = ref("");

    const category_id = route.query.categoryId;
    const menu_id = route.query.menuId;
    const record_state_id = route.query.recordId;

    const thisTotalSet = ref("");
    const beforeTotalSet = ref("");

    const msgNoBeforeData = ref("");

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

    const { getLoginUser, loginUser } = useGetLoginUser();

    //前回のデータを取得
    const {
      secondRecord,
      hasSecondRecord,
      getSecondRecord,
    } = useGetSecondRecordContent();

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
        route.params.recordId
      );
      if (hasSecondRecord.value) {
        isBeforeData.value = true;
        beforeBodyWeight.value = secondRecord.bodyWeight;
      } else {
        msgNoBeforeData.value = "前回の記録はありません";
      }
    };

    //第一引数に子供の値が入っている。
    const fillThisTodalSet = (e) => {
      thisTotalSet.value = e;
    };

    const fillBeforeTodalSet = (e) => {
      beforeTotalSet.value = e;
    };

    onMounted(async () => {
      await getLoginUser();
      await getLatestRecordState();
      await getMenuContent();

      if (latestRecord.value.bodyWeight) {
        bodyWeight.value = `${latestRecord.value.bodyWeight} kg`;
      } else {
        // bodyWeight.value = "記録されていません";
      }
    });

    return {
      header,
      thisTotalSet,
      beforeTotalSet,
      isBeforeData,
      msgNoBeforeData,
      hasOneHand,
      bodyWeight,
      beforeBodyWeight,
      category_id,
      menu_id,
      record_state_id,
      secondRecord,
      hasSecondRecord,
      fillBeforeRecord,
      fillThisTodalSet,
      fillBeforeTodalSet,
    };
  },
};
</script>

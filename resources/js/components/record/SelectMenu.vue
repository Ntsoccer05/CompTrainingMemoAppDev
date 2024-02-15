<template>
  <div>
    <h3 class="text-lg text-center font-bold mt-6">{{ dispHeadText }}</h3>
    <button
      class="block w-50 bg-blue-500 hover:bg-blue-700 text-white font-bold md:py-2 py-px px-4 border-2 border-black mt-3 mb-3 ml-auto rounded-full mr-5"
      @click="toAddMenu()"
    >
      部位・種目を追加する
    </button>
    <button
      :class="[
        'w-50 bg-red-500 hover:bg-red-700 text-white font-bold md:py-2 py-px px-4 border-2 border-black mt-3 mb-3 ml-auto rounded-full mr-5',
        editable ? 'hidden' : 'block',
      ]"
      @click="editMenu()"
    >
      編集／削除する
    </button>
    <button
      :class="[
        'w-50 bg-red-500 hover:bg-red-700 text-white font-bold md:py-2 py-px px-4 border-2 border-black mt-3 mb-3 ml-auto rounded-full mr-5',
        editable ? 'block' : 'hidden',
      ]"
      @click="compEditMenu()"
    >
      編集／削除を終了する
    </button>
    <template v-if="hasRecord">
      <div class="text-right mr-5 md:mr-10">
        <i class="fa-solid fa-minus text-green-400 text-xl"></i>
        <span class="text-lg">：記録済み</span>
      </div>
    </template>
    <div :class="['text-right mr-5 md:mr-10', editable ? 'block' : 'hidden']">
      <div><i class="fa-solid fa-trash"></i><span class="font-bold">：削除</span></div>
      <div>
        <span class="font-bold text-red-600">※部位を削除すると種目も削除されます</span>
      </div>
    </div>
    <div class="mx-auto w-11/12 mt-5 mb-5">
      <label for="weight block sm:inline"> 体重：</label>
      <input
        class="border border-black text-right w-28"
        name="weight"
        type="text"
        placeholder="kg"
        maxlength="6"
        :value="weight"
        @change="weight = validateWeight($event.target.value)"
        @blur="postWeight"
      />
    </div>

    <!-- :子供のprops名：データ値 -->
    <EditableMenuTable
      :dispHeadText="dispHeadText"
      :editable="editable"
      :records="records"
      :dataMenu="dataMenu"
    />
  </div>
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
</template>

<script>
import EditableMenuTable from "./EditableMenuTable.vue";
import { ref, onMounted, watch, computed } from "vue";
import { useStore } from "vuex";
import { useRoute, useRouter, onBeforeRouteLeave } from "vue-router";
import useGetLoginUser from "../../composables/certification/useGetLoginUser.js";
import useGetRecordState from "../../composables/record/useGetRecordState";
import useGetRecords from "../../composables/record/useGetRecords";

export default {
  components: {
    EditableMenuTable,
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const store = useStore();

    store.commit("compGetData", false);

    const dispModal = computed(() => store.getters.dispAlertModal);

    const dispHeadText = ref("");

    const recorded_day = route.params.recordId;

    const weight = ref(null);

    let dataCategory = ref([]);
    let dataMenu = ref([]);

    const editable = ref(false);
    const hasCategory = ref(true);
    const hasMenu = ref(true);
    const hasRecord = ref(false);
    // DOM取得のため
    const deleteFunc = ref(null);
    const deleteCategory = ref(null);

    const dispAlertModal = ref(false);

    const recorded_at = ref("");

    const { getLoginUser, loginUser } = useGetLoginUser();

    const { getLatestRecordState, latestRecord } = useGetRecordState();

    const { records, getRecords } = useGetRecords();

    watch(records, () => {
      records.value.forEach((record) => {
        if (record.category) {
          dataCategory.value.push(record.category[0].category_id);
        }
        if (record.menu) {
          hasRecord.value = true;
          for (const index in record.menu) {
            dataMenu.value.push(record.menu[index].menu_id);
          }
        }
        if (record.recorded_at.recorded_at) {
          let formatRecord = ref("");
          formatRecord.value = record.recorded_at.recorded_at.split("-");
          recorded_at.value =
            formatRecord.value[0] &
            "年" &
            formatRecord.value[1] &
            "月" &
            formatRecord.value[2];
        }
      });
    });

    const toHome = () => {
      //router.pushが効かない
      window.location.href = "/";
    };

    const toLogin = () => {
      router.push("/login");
    };

    //トレーニングメニュー追加画面に遷移
    const toAddMenu = () => {
      router.push({ name: "addMenu", params: { recordId: recorded_day } });
    };

    const editMenu = () => {
      editable.value = true;
      dispHeadText.value = "編集する部位・種目を選択してください";
    };

    const compEditMenu = () => {
      editable.value = false;
      dispHeadText.value = "";
      getMenus();
      if (hasCategory.value === false) {
        dispHeadText.value = "部位・種目を追加してください";
      } else if (hasMenu.value === false) {
        dispHeadText.value = "種目を追加してください";
      } else {
        dispHeadText.value = "鍛える部位を選択してください";
      }
    };

    //メニュー内容を取得
    const getMenus = async () => {
      await axios
        .get("/api/menus", {
          // get時にパラメータを渡す際はparamsで指定が必要
          params: {
            user_id: loginUser.value.id,
          },
        })
        .then((res) => {
          //編集画面でなければ
          if (!editable.value) {
            if (res.data.categories.length === 0) {
              dispHeadText.value = "部位・種目を追加してください";
              hasCategory.value = false;
              hasMenu.value = false;
            } else if (res.data.menulist.length === 0) {
              dispHeadText.value = "種目を追加してください";
              hasMenu.value = false;
              hasCategory.value = true;
            } else {
              dispHeadText.value = "鍛える部位を選択してください";
              hasCategory.value = true;
              hasMenu.value = true;
            }
            if (res.data.categorylist.length === 0) {
              dispHeadText.value = "部位・種目を追加してください";
              hasCategory.value = false;
              hasMenu.value = false;
            } else if (res.data.menulist2[0].length === 0) {
              dispHeadText.value = "種目を追加してください";
              hasMenu.value = false;
              hasCategory.value = true;
            } else {
              dispHeadText.value = "鍛える部位を選択してください";
              hasCategory.value = true;
              hasMenu.value = true;
            }
          }
        })
        .catch((err) => {
          console.log(err);
        });
    };

    //体重を記録する
    const postWeight = async () => {
      await axios
        .post("/api/record/edit", {
          user_id: loginUser.value.id,
          recording_day: latestRecord.value.recorded_at,
          weight: weight.value,
        })
        .then((res) => {
          console.log(res);
        })
        .catch((err) => {
          console.log(err);
        });
    };

    //全角→半角
    const replaceFullToHalf = (str) => {
      return str.replace(/[！-～]/g, function (s) {
        return String.fromCharCode(s.charCodeAt(0) - 0xfee0);
      });
    };

    // valはString
    const validateWeight = (val) => {
      val = replaceFullToHalf(val);
      // 小数点を含むか？
      let oldVal = val;
      const decPoint = oldVal.indexOf(".");
      // replaceは型がStringのもののみ適用できる(replaceはそのものの値自体は変えないので代入する必要あり)
      // 数字または小数点以外を無効とする
      val = val.replace(/[^0-9|.]/g, "");
      // val = val.replace(/\D/g, "");
      // parseFloatで整数型へ変換している
      if (val !== "") {
        val = parseFloat(val);
        // toFixedで小数第一位で四捨五入する
        val = parseFloat(val.toFixed(1));
        // matchは型がStringのもののみ適用できる
        val.toString().match(/^(\d+)(\.\d*)?/u) ? val : "";
      }
      return val;
    };

    onMounted(async () => {
      // DOM取得のため
      const deleteFuncDom = deleteFunc.value;
      const deleteCategoryDom = deleteCategory.value;

      await getLoginUser();
      if (dispModal.value) {
        dispAlertModal.value = true;
      }
      if (route.params.recordId) {
        await getRecords(loginUser.value.id, recorded_day).then((res) => {
          store.commit("compGetData", true);
        });
      } else if (loginUser.value.id) {
        await getRecords(loginUser.value.id).then((res) => {
          store.commit("compGetData", true);
        });
      }
      await getLatestRecordState();
      await getMenus();
      if (latestRecord.value.bodyWeight) {
        weight.value = latestRecord.value.bodyWeight;
      }
    });

    //遷移前処理
    const deleteMenu = async (next) => {
      await axios
        .post("/api/record/destroy", {
	  user_id: loginUser.value.id,
          recorded_at: recorded_day,
        })
        .then((res) => {
          next();
        })
        .catch(() => {});
    };

    //遷移前処理
    onBeforeRouteLeave((to, from, next) => {
      store.commit("compGetData", false);
      if (to.name === "home") {
        if (records.value.length === 0) {
          deleteMenu(next);
        } else if (records.value.length === 1 && !records.value[0].category) {
          deleteMenu(next);
        } else {
          next();
        }
      } else {
        next();
      }
    });

    return {
      dispHeadText,
      editable,
      weight,
      records,
      recorded_at,
      dataMenu,
      dataCategory,
      hasRecord,
      // DOM取得のため
      deleteFunc,
      deleteCategory,
      // メソッド
      toAddMenu,
      editMenu,
      compEditMenu,
      postWeight,
      validateWeight,
      dispAlertModal,
      toHome,
      toLogin,
    };
  },
};
</script>

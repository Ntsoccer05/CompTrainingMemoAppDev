<template>
  <div>
    <thead>
      <tr class="grid grid-cols-2 w-2full">
        <th class="mx-auto block w-full col-span-2">{{ menuContent }}</th>
      </tr>
      <tr>
        <th class="text-left md:text-center indent-1 md:indent-0">
          <div class="border" ref="todayRecordedAt">今回の記録</div>
        </th>
        <th class="text-left md:text-center indent-1 md:indent-0">
          <div class="border" ref="beforeRecordedAt">{{ beforeHeaderTxt }}</div>
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="index in maxSet" :key="index">
        <td @keyup.enter="nextInputFocus($event)">
          <div class="bg-gray-200 border indent-1">{{ index + 1 }}セット目</div>
          <div :class="hasOneHand ? 'hidden' : 'block'">
            <!-- changeだとfocusが外れた時、inputは入力したとき -->
            <!-- v-modelは:valueと@changeで表せる -->
            <input
              class="border w-full pl-0.5"
              type="text"
	      name="weight"
              placeholder="重さ(kg)"
              maxlength="6"
              :value="weight[index]"
	      @focus="complementData($event.target.value, weight, index)"
              @change="validateDecimalNumber($event.target.value, weight, index)"
              @blur="postRecordContent(index)"
            />
            <input
              class="border w-full pl-0.5"
              type="text"
	      name="rep"
              placeholder="回数"
              maxlength="3"
              :value="rep[index]"
	      @focus="complementData($event.target.value, rep, index)"
              @change="validateNumber($event.target.value, rep, index)"
              @blur="postRecordContent(index)"
            />
          </div>
          <div :class="hasOneHand ? 'block' : 'hidden'">
            <input
              class="border w-full pl-0.5"
              type="text"
	      name="rightWeight"
              placeholder="重さ（右）(kg)"
              maxlength="6"
              :value="rightWeight[index]"
	      @focus="complementData($event.target.value, rightWeight, index)"
              @change="validateDecimalNumber($event.target.value, rightWeight, index)"
              @blur="postRecordContent(index)"
            />
            <input
              class="border w-full pl-0.5"
              type="text"
	      name="rightRep"
              placeholder="回数（右）"
              maxlength="3"
              :value="rightRep[index]"
	      @focus="complementData($event.target.value, rightRep, index)"
              @change="validateNumber($event.target.value, rightRep, index)"
              @blur="postRecordContent(index)"
            />
            <input
              class="border w-full pl-0.5"
              type="text"
	      name="leftWeight"
              placeholder="重さ（左）(kg)"
              maxlength="6"
              :value="leftWeight[index]"
	      @focus="complementData($event.target.value, leftWeight, index)"
              @change="validateDecimalNumber($event.target.value, leftWeight, index)"
              @blur="postRecordContent(index)"
            />
            <input
              class="border w-full pl-0.5"
              type="text"
	      name="leftRep"
              placeholder="回数（左）"
              maxlength="3"
              :value="leftRep[index]"
	      @focus="complementData($event.target.value, leftRep, index)"
              @change="validateNumber($event.target.value, leftRep, index)"
              @blur="postRecordContent(index)"
            />
          </div>
          <div class="border">
            <textarea
              class="w-full leading-4 pl-0.5"
	      name="memo"
              v-model="memo[index]"
              cols="20"
              rows="4"
              placeholder="メモ"
              @blur="postRecordContent(index)"
	      ref="thisMemo"
              @input="
                                adjustHeight(
                                    $event.target,
                                    beforeMemo[index]
                                )
                            "
            ></textarea>
          </div>
        </td>
        <td>
          <div class="bg-gray-200 border indent-1">{{ index + 1 }}セット目</div>
          <div :class="hasOneHand ? 'hidden' : 'block'">
            <!-- readonlyだとfocusできるが、disabledだとfocusもできない -->
            <!-- inputのvalue値にデータを入力するにはv-bindを用いる -->
            <input
              class="border w-full"
              type="text"
              placeholder="重さ(kg)"
              :value="contents[index].set ? contents[index].weight : ''"
              disabled
            />
            <input
              class="border w-full"
              type="text"
              placeholder="回数"
              :value="contents[index].set ? contents[index].rep : ''"
              disabled
            />
          </div>
          <div :class="hasOneHand ? 'block' : 'hidden'">
            <input
              class="border w-full"
              type="text"
              placeholder="重さ（右）(kg)"
              :value="contents[index].set ? contents[index].right_weight : ''"
              disabled
            />
            <input
              class="border w-full"
              type="text"
              placeholder="回数（右）"
              :value="contents[index].set ? contents[index].right_rep : ''"
              disabled
            />
            <input
              class="border w-full"
              type="text"
              placeholder="重さ（左）(kg)"
              :value="contents[index].set ? contents[index].left_weight : ''"
              disabled
            />
            <input
              class="border w-full"
              type="text"
              placeholder="回数（左）"
              :value="contents[index].set ? contents[index].left_rep : ''"
              disabled
            />
          </div>
          <div class="border">
            <textarea
              class="w-full leading-4 pl-0.5"
              name=""
              id=""
              cols="20"
              rows="4"
              placeholder="メモ"
              :value="contents[index].set ? inputBeforeMemo(index) : ''"
              disabled
              ref="beforeMemo"
            ></textarea>
          </div>
        </td>
      </tr>
    </tbody>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from "vue";
import { useRoute, onBeforeRouteLeave } from "vue-router";
import useGetLoginUser from "../../composables/certification/useGetLoginUser.js";
import useGetTgtRecordContent from "../../composables/record/useGetTgtRecordContent.js";
import axios from "axios";
import { useStore } from "vuex";

export default {
  props: {
    second_record: [Object, String],
    hasSecondRecord: Boolean,
    hasOneHand: Boolean,
    category_id: String,
    menu_id: String,
    record_state_id: String,
    menu_content: String,
    beforeHeaderTxt: String,
    complementContents: Boolean,
  },
  setup(props, { emit }) {
    const route = useRoute();
    const store = useStore();
    store.commit("compGetData", false);

    const hasOneHand = computed(() => props.hasOneHand);
    const second_record = computed(() => props.second_record);
    const menuContent = computed(() => props.menu_content);
    const complementContents = computed(() => props.complementContents);
    const beforeHeaderTxt = computed(() => props.beforeHeaderTxt);
    const weight = ref([]);
    const rep = ref([]);
    const rightWeight = ref([]);
    const rightRep = ref([]);
    const leftWeight = ref([]);
    const leftRep = ref([]);
    const memo = ref([]);
    const doRecord = ref(false);
    const doDelete = ref(false);
    //配列作成
　　const maxSet = Array(10)
      .fill(0)
      .map((_, i) => i);

    const maxBeforeLength = ref("");

    const canClickFillBefore = ref(false);

    // DOM要素を取得するにはref属性と同じ名前にする
    const beforeMemo = ref(null);
    const thisMemo = ref(null);

    // メニューはセレクトボックス、休憩時間はタイムピッカー
    const header = {
      set: "セット数",
      menu: "メニュー",
      weight: "重量(kg)",
      rep: "回数",
      rest: "休憩時間",
    };

    const isDisabled = ref(false);

    //ログインユーザー情報取得
    const { getLoginUser, loginUser } = useGetLoginUser();

    //今回記録するデータの値を取得
    const { tgtRecord, hasTgtRecord, getTgtRecords } = useGetTgtRecordContent();

    const contents = ref([
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
      { set: 0, menu: "", weight: 0, rep: 0, rest: 0 },
    ]);
    // watchは引数を二つ持つ(一つ目：監視対象、二つ目：新しい値と古い値)
    watch(second_record, () => {
      if (props.hasSecondRecord) {
        maxBeforeLength.value = 0;
        second_record.value.forEach((record) => {
          const index = record.set - 1;
          contents.value[index] = record;
          if (maxBeforeLength.value < record.set) {
            maxBeforeLength.value = record.set;
          }
        });
        //emit()で親に値を渡す、第一引数：親側の@～の～の名前、第二引数：親に渡す値
        emit("beforeTotalSet", second_record.value.length);
        if (maxBeforeLength.value < 5) {
          const tempObj = ref([]);
          for (let i = 1; i <= 5 - maxBeforeLength.value; i++) {
            tempObj.value[i] = { set: maxBeforeLength.value };
            contents.value = [...contents.value, tempObj.value[i]];
          }
        } else {
          contents.value.set = contents.value.set;
        }
      }
    });

    //全角→半角
    const replaceFullToHalf = (str) => {
      return str.replace(/[！-～]/g, function (s) {
        return String.fromCharCode(s.charCodeAt(0) - 0xfee0);
      });
    };

    // valはString
    const validateDecimalNumber = (val, tgtVal, index) => {
      // tgtvalの変更がない場合代入してもvalueの値が変化しないため
      tgtVal[index] = val;
      val = replaceFullToHalf(val);
      val = replaceFullToHalf(val);
      // 小数点を含むか？
      let oldVal = val;
      const decPoint = oldVal.indexOf(".");
      // replaceは型がStringのもののみ適用できる(replaceはそのものの値自体は変えないので代入する必要あり)
      // 数字または小数点以外を無効とする
      val = val.replace(/[^0-9|.]/g, "");
      // val = val.replace(/\D/g, "");
      // parseFloatで少数型へ変換している
      if (val !== "") {
        val = parseFloat(val);
        // toFixedで小数第一位で四捨五入する
        val = parseFloat(val.toFixed(1));
        // matchは型がStringのもののみ適用できる
        val.toString().match(/^(\d+)(\.\d*)?/u) ? val : "";
      }
      tgtVal[index] = val.toString();
    };

    const validateNumber = (val, tgtVal, index) => {
      // tgtvalの変更がない場合代入してもvalueの値が変化しないため
      tgtVal[index] = val;
      val = replaceFullToHalf(val);
      // 数字または小数点以外を無効とする
      val = val.replace(/[^0-9]/g, "");
      // parseFloatで少数型へ変換している
      if (val !== "") {
        val = parseFloat(val);
        // toFixedで小数第一位で四捨五入する
        val = parseFloat(val.toFixed(1));
        // matchは型がStringのもののみ適用できる
        val.toString().match(/^(\d+)(\.\d*)?/u) ? val : "";
      }
      tgtVal[index] = val.toString();
    };

    const postRecordContent = (index) => {
      axios
        .post("/api/recordContent/create", {
          user_id: loginUser.value.id,
          category_id: route.query.categoryId,
          menu_id: route.query.menuId,
          record_state_id: route.query.recordId,
          recorded_at: route.params.recordId,
          weight: weight.value[index],
          right_weight: rightWeight.value[index],
          right_rep: rightRep.value[index],
          left_weight: leftWeight.value[index],
          left_rep: leftRep.value[index],
          rep: rep.value[index],
          set: index + 1,
          memo: memo.value[index],
        })
        .then((res) => {
	  canClickFillBefore.value = true;
          // 今回の合計セット数
          // emit()で親に値を渡す、第一引数：親側の@～の～の名前、第二引数：親に渡す値
          emit("totalSet", res.data.totalSet);
	  if (res.data.totalSet > 0) {
            canClickFillBefore.value = true;
          } else {
            canClickFillBefore.value = false;
          }
          emit("canClick", canClickFillBefore.value);
        })
        .catch((err) => {
          console.log(err);
        });
    };

    // 重量と回数を自動補完
　　const complementData = (val, tgtVal, index) => {
      if (
        index - 1 > -1 &&
        complementContents.value &&
        tgtVal[index - 1] &&
        (tgtVal[index] == "" || tgtVal[index] == undefined)
      ) {
        val = tgtVal[index - 1].toString();
        tgtVal[index] = tgtVal[index - 1];
	postRecordContent(index);
      } else {
        tgtVal[index];
      }
    };

    //tgtRecordを初期レンダリング時に取得するため、変更を常にwatchする。
    watch(tgtRecord, () => {
      if (hasTgtRecord.value) {
	canClickFillBefore.value = true;
        emit("canClick", canClickFillBefore.value);
        //emit()で親に値を渡す、第一引数：親側の@～の～の名前、第二引数：親に渡す値
        emit("totalSet", tgtRecord.value.length);
        tgtRecord.value.forEach((record) => {
          const index = record.set - 1;
          weight.value[index] = record.weight !== null ? record.weight : "";
          rep.value[index] = record.rep !== null ? record.rep : "";
          rightWeight.value[index] =
            record.right_weight !== null ? record.right_weight : "";
          rightRep.value[index] = record.right_rep !== null ? record.right_rep : "";
          leftWeight.value[index] = record.left_weight !== null ? record.left_weight : "";
          leftRep.value[index] = record.left_rep !== null ? record.left_rep : "";
          memo.value[index] = record.memo !== null ? record.memo : "";
          if (record.set > 5) {
            const tempObj = ref([]);
            for (let i = 6; i <= record.set; i++) {
              tempObj.value[i] = { set: record.set + i };
              contents.value = [...contents.value, tempObj.value[i]];
            }
          }
        });
      } else {
        canClickFillBefore.value = false;
        emit("canClick", canClickFillBefore.value);
      }
    });

    // エンターキーを押すと次の入力要素フォーカス
    const nextInputFocus = (e) => {
      const inputTags = e.currentTarget.querySelectorAll("input");
      const textareaTags = e.currentTarget.querySelectorAll(
        "textarea"
      );

      for (let index = 0; index <= inputTags.length - 1; index++) {
        //通常の場合
        if (index === 0 && inputTags[index] === document.activeElement) {
          // インプットエリアにフォーカス
          inputTags[index + 1].focus();
          break;
        } else if (index === 1 && inputTags[index] === document.activeElement) {
          // テキストエリアにフォーカス
          textareaTags[0].focus();
          break;
        } else if (
          2 <= index &&
          index < inputTags.length - 1 &&
          inputTags[index] === document.activeElement
        ) {
          // 片方ずつ記録する場合
          inputTags[index + 1].focus();
          break;
        } else if (
          index === inputTags.length - 1 &&
          inputTags[index] === document.activeElement
        ) {
          // テキストエリアにフォーカス
          textareaTags[0].focus();
          break;
        }
      }
    };

    // 高さを調整する関数
const adjustHeight = (
  element,
  tgtTxtElm = null
) => {
  // 現在の高さを取得
  const currentHeight = element.offsetHeight;
  const currentTgtHeight = tgtTxtElm.offsetHeight;
  const newHeight = element.scrollHeight;
  // 新しい高さが現在の高さより小さくない場合のみ、高さを更新
  if (newHeight > Math.max(currentHeight, currentTgtHeight)) {
    element.style.height = `${newHeight}px`; // スクロールの高さに基づいて高さを設定
    tgtTxtElm.style.height = `${newHeight}px`; // スクロールの高さに基づいて高さを設定
  } else {
    element.style.height = `${Math.max(currentHeight, currentTgtHeight)}px`;
    tgtTxtElm.style.height = `${Math.max(currentHeight, currentTgtHeight)}px`;
  }
};

const inputBeforeMemo = (index) => {
  beforeMemo.value[index].value = contents.value[index].memo;
  adjustHeight(beforeMemo.value[index], thisMemo.value[index]);
  return contents.value[index].memo;
};

    onMounted(async () => {
      await getLoginUser();
      await getTgtRecords(
        loginUser.value.id,
        props.category_id,
        props.menu_id,
        props.record_state_id
      );
      if (tgtRecord.value.length == 0) {
        emit("totalSet", "0");
      }
      store.commit("compGetData", true);
      thisMemo.value &&
      thisMemo.value.forEach((elm, index) => {
        elm.value !== "" && adjustHeight(elm, beforeMemo.value[index]);
      });
    });

    // 戻るボタン押下時に入力中内容を保存する
    onBeforeRouteLeave(async (to, from, next) => {
      // 現在フォーカスが当たっている要素
      const activeElem = document.activeElement;
      // セット数
      let index = -1;

      if (activeElem.tagName == "INPUT") {
        if (activeElem) {
          switch (activeElem.name) {
            case "weight":
            case "rep":
              index =
                Number(
                  activeElem.parentElement.previousElementSibling.innerHTML.slice(0, 1)
                ) - 1;
              if (activeElem.name == "weight") {
                validateDecimalNumber(
                  activeElem.value,
                  weight.value,
                  index
                );
              } else if (activeElem.name == "rep") {
                validateNumber(activeElem.value, rep.value, index);
              }
              break;
            case "rightWeight":
            case "rightRep":
            case "leftWeight":
            case "leftRep":
              index =
                Number(
                  activeElem.parentElement.previousElementSibling.previousElementSibling.innerHTML.slice(
                    0,
                    1
                  )
                ) - 1;
              if (activeElem.name == "rightWeight") {
                validateDecimalNumber(activeElem.value, rightWeight.value, index)
              } else if (activeElem.name == "rightRep") {
                validateNumber(activeElem.value, rightRep.value, index)
              } else if (activeElem.name == "leftWeight") {
                validateDecimalNumber(activeElem.value, leftWeight.value, index)
              } else if (activeElem.name == "leftRep") {
                validateNumber(activeElem.value, leftRep.value, index)
              }
              break;
          }
        }
        if (index > -1) {
          postRecordContent(index);
        }
      } else if (activeElem.tagName === "TEXTAREA") {
        if (activeElem) {
          switch (activeElem.name) {
            case "memo":
              index =
                Number(
                  activeElem.parentElement.parentElement.firstElementChild.innerHTML.slice(
                    0,
                    1
                  )
                ) - 1;
              break;
          }
          if (index > -1) {
            postRecordContent(index);
          }
        }
    }
      next();
    });

    return {
      weight,
      rep,
      rightWeight,
      rightRep,
      leftWeight,
      leftRep,
      memo,
      header,
      beforeMemo,
      thisMemo,
      contents,
      hasOneHand,
      route,
      maxSet,
      menuContent,
      beforeHeaderTxt,
      complementContents,
      validateNumber,
      validateDecimalNumber,
      postRecordContent,
      complementData,
　　　nextInputFocus,
      adjustHeight,
      inputBeforeMemo,
    };
  },
};
</script>

<style></style>

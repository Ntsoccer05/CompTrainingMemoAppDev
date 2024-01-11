<template>
  <div>
    <thead>
      <tr>
        <th class="text-left md:text-center indent-1 md:indent-0">
          <div class="border" ref="todayRecordedAt">今回の記録</div>
        </th>
        <th class="text-left md:text-center indent-1 md:indent-0">
          <div class="border" ref="beforeRecordedAt">前回の記録</div>
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(content, index) in contents" :key="index">
        <td>
          <div class="bg-gray-200 border indent-1">{{ index + 1 }}セット目</div>
          <div :class="hasOneHand ? 'hidden' : 'block'">
            <!-- changeだとfocusが外れた時、inputは入力したとき -->
            <!-- v-modelは:valueと@changeで表せる -->
            <input
              class="border w-full"
              type="text"
              placeholder="重さ(kg)"
              maxlength="6"
              :value="weight[index]"
              @change="weight[index] = validateDecimalNumber($event.target.value)"
              @blur="postRecordContent(index)"
            />
            <input
              class="border w-full"
              type="text"
              placeholder="回数"
              maxlength="6"
              :value="rep[index]"
              @change="rep[index] = validateNumber($event.target.value)"
              @blur="postRecordContent(index)"
            />
          </div>
          <div :class="hasOneHand ? 'block' : 'hidden'">
            <input
              class="border w-full"
              type="text"
              placeholder="重さ（右）(kg)"
              maxlength="6"
              :value="rightWeight[index]"
              @change="rightWeight[index] = validateDecimalNumber($event.target.value)"
              @blur="postRecordContent(index)"
            />
            <input
              class="border w-full"
              type="text"
              placeholder="回数（右）"
              maxlength="6"
              :value="rightRep[index]"
              @change="rightRep[index] = validateNumber($event.target.value)"
              @blur="postRecordContent(index)"
            />
            <input
              class="border w-full"
              type="text"
              placeholder="重さ（左）(kg)"
              maxlength="6"
              :value="leftWeight[index]"
              @change="leftWeight[index] = validateDecimalNumber($event.target.value)"
              @blur="postRecordContent(index)"
            />
            <input
              class="border w-full"
              type="text"
              placeholder="回数（左）"
              maxlength="6"
              :value="leftRep[index]"
              @change="leftRep[index] = validateNumber($event.target.value)"
              @blur="postRecordContent(index)"
            />
          </div>
          <div class="border">
            <textarea
              class="w-full leading-3"
              v-model="memo[index]"
              cols="20"
              rows="4"
              placeholder="メモ"
              @blur="postRecordContent(index)"
            ></textarea>
          </div>
        </td>
        <td>
          <div class="bg-gray-200 border indent-1">{{ index + 1 }}セット目</div>
          <template v-if="content.set">
            <div :class="hasOneHand ? 'hidden' : 'block'">
              <!-- readonlyだとfocusできるが、disabledだとfocusもできない -->
              <!-- inputのvalue値にデータを入力するにはv-bindを用いる -->
              <input
                class="border w-full"
                type="text"
                placeholder="重さ(kg)"
                :value="content.weight"
                disabled
              />
              <input
                class="border w-full"
                type="text"
                placeholder="回数"
                :value="content.rep"
                disabled
              />
            </div>
            <div :class="hasOneHand ? 'block' : 'hidden'">
              <input
                class="border w-full"
                type="text"
                placeholder="重さ（右）(kg)"
                :value="content.right_weight"
                disabled
              />
              <input
                class="border w-full"
                type="text"
                placeholder="回数（右）"
                :value="content.right_rep"
                disabled
              />
              <input
                class="border w-full"
                type="text"
                placeholder="重さ（左）(kg)"
                :value="content.left_weight"
                disabled
              />
              <input
                class="border w-full"
                type="text"
                placeholder="回数（左）"
                :value="content.left_rep"
                disabled
              />
            </div>
            <div class="border">
              <textarea
                class="w-full leading-3"
                name=""
                id=""
                cols="20"
                rows="4"
                placeholder="メモ"
                :value="content.memo"
                disabled
              ></textarea>
            </div>
          </template>
          <template v-else>
            <div :class="hasOneHand ? 'hidden' : 'block'">
              <input
                class="border w-full"
                type="text"
                placeholder="重さ(kg)"
                ref="beforeWeight"
                disabled
              />
              <input
                class="border w-full"
                type="text"
                placeholder="回数"
                ref="beforeReps"
                disabled
              />
            </div>
            <div :class="hasOneHand ? 'block' : 'hidden'">
              <input
                class="border w-full"
                type="text"
                placeholder="重さ（右）(kg)"
                ref="beforeRightWeight"
                disabled
              />
              <input
                class="border w-full"
                type="text"
                placeholder="回数（右）"
                ref="beforeRightReps"
                disabled
              />
              <input
                class="border w-full"
                type="text"
                placeholder="重さ（左）(kg)"
                ref="beforeLeftWeight"
                disabled
              />
              <input
                class="border w-full"
                type="text"
                placeholder="回数（左）"
                ref="beforeLeftReps"
                disabled
              />
            </div>
            <div class="border">
              <textarea
                class="w-full leading-3"
                name=""
                id=""
                cols="20"
                rows="4"
                placeholder="メモ"
                disabled
                ref="beforeMemo"
              ></textarea>
            </div>
          </template>
        </td>
      </tr>
    </tbody>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from "vue";
import { useRoute } from "vue-router";
import useGetLoginUser from "../../composables/certification/useGetLoginUser.js";
import useGetTgtRecordContent from "../../composables/record/useGetTgtRecordContent.js";
import axios from "axios";
// エンターキーを押すと次の要素入力可
function keydown(e) {
  if (e.keyCode === 13) {
    var obj = document.activeElement;
    if (obj.nextElementSibling) {
      obj.nextElementSibling.focus();
    } else if (obj.parentNode.nextSibling) {
      if (obj.parentNode.nextSibling.children) {
        if (obj.parentNode.nextSibling.children[0].nodeName == "TEXTAREA") {
          obj.parentNode.nextSibling.children[0].focus();
        }
        if (obj.parentNode.nextSibling.children[2]) {
          if (obj.parentNode.nextSibling.children[2].parentNode.nextSibling.children) {
            if (
              obj.parentNode.nextSibling.children[2].parentNode.nextSibling.children[0]
                .nodeName == "TEXTAREA"
            ) {
              obj.parentNode.nextSibling.children[2].parentNode.nextSibling.children[0].focus();
              // 1行目指定のため(無いと2行目指定となる)
              e.returnValue = false;
            }
          }
        }
      }
    }
  }
}

window.onkeydown = keydown;
export default {
  props: {
    second_record: [Object, String],
    hasSecondRecord: Boolean,
    hasOneHand: Boolean,
    category_id: String,
    menu_id: String,
    record_state_id: String,
  },
  setup(props, { emit }) {
    const route = useRoute();
    const hasOneHand = computed(() => props.hasOneHand);
    const second_record = computed(() => props.second_record);
    const weight = ref([]);
    const rep = ref([]);
    const rightWeight = ref([]);
    const rightRep = ref([]);
    const leftWeight = ref([]);
    const leftRep = ref([]);
    const memo = ref([]);
    const doRecord = ref(false);
    const doDelete = ref(false);

    const maxBeforeLength = ref("");

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
    const validateDecimalNumber = (val) => {
      val = replaceFullToHalf(val);
      // 小数点を含むか？
      let oldVal = val;
      const decPoint = oldVal.indexOf(".");
      // replaceは型がStringのもののみ適用できる(replaceはそのものの値自体は変えないので代入する必要あり)
      // 数字または小数点以外を無効とする
      val = val.replace(/[^0-9|.]/g, "");
      // val = val.replace(/\D/g, "");
      if (decPoint !== -1) {
        val = val / 10 ** (decPoint + 1);
      }
      // parseFloatで少数型へ変換している
      if (val !== "") {
        val = parseFloat(val);
        // toFixedで小数第一位で四捨五入する
        val = parseFloat(val.toFixed(1));
        // matchは型がStringのもののみ適用できる
        val.toString().match(/^(\d+)(\.\d*)?/u) ? val : "";
      }
      return val;
    };

    const validateNumber = (val) => {
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
      return val;
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
          console.log(res.data.totalSet);
          // 今回の合計セット数
          // emit()で親に値を渡す、第一引数：親側の@～の～の名前、第二引数：親に渡す値
          emit("totalSet", res.data.totalSet);
        })
        .catch((err) => {
          console.log(err);
        });
    };

    //tgtRecordを初期レンダリング時に取得するため、変更を常にwatchする。
    watch(tgtRecord, () => {
      if (hasTgtRecord.value) {
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
      }
    });

    onMounted(async () => {
      await getLoginUser();
      await getTgtRecords(
        loginUser.value.id,
        props.category_id,
        props.menu_id,
        props.record_state_id
      );
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
      contents,
      hasOneHand,
      route,
      validateNumber,
      validateDecimalNumber,
      postRecordContent,
    };
  },
};
</script>

<style></style>

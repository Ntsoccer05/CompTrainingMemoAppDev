import axios from 'axios';
import { createStore } from 'vuex';
import useNotLoginedRedirect from './composables/certification/useNotLoginedRedirect.js';

export default createStore({
    state:{
        user:[],
        isLogined: false,
        day: "",
        latestRecordState:"",
        latestRecordMenus:"",
        recorded_at:"",
	compGetData: false,
	dispAlertModal: false
    },
    getters:{
      isLogined:state => state.isLogined,
      loginUser:state => state.user,
      selectedDay:state => state.day,
      latestRecord:state=>state.latestRecordState,
      latestMenus:state=>state.latestRecordMenus,
      getRecordedAt:state=>state.recorded_at,
      compGetData:state=>state.compGetData,
      dispAlertModal:state=>state.dispAlertModal,
    },
    mutations:{
      LoginState(state){
        // ログイン状態
        state.isLogined = true;
      },
      LogoutState(state){
        // ログアウト状態
        state.isLogined = false;
      },
      selectedDay(state, day){
        state.day = day
      },
      loginUser(state, user){
        state.user = user
      },
      latestRecordState(state,latestRecordState){
        state.latestRecordState = latestRecordState
      },
      setRecordedAt(state, recordedAt){
        state.recorded_at = recordedAt
      },
      compGetData(state, status){
        state.compGetData = status
      },
      dispAlertModal(state, status){
        state.dispAlertModal = status
      }
    },
    actions:{
        async loginState({state}) {
        await axios.get("/api/users")
          .then((res) => {
            // ログイン状態取得
            state.isLogined = true;
            state.dispAlertModal = false;
            // ログインしているユーザー情報取得
            state.user = res.data;
          })
          .catch((err) => {
            // ログイン状態取得
            state.isLogined = false;
	    const {dispAlert} = useNotLoginedRedirect(err);
            if(dispAlert.value= true){
              state.dispAlertModal = true
            }
          })
        },

        async getLoginUser({state}) {
          await axios.get("/api/users")
          .then((res) => {
            state.dispAlertModal = false
            // ログインしているユーザー情報取得
            state.user = res.data;
          })
          .catch((err) => {
            // ログインしていない状態だとホーム画面へリダイレクト
            const {dispAlert} = useNotLoginedRedirect(err);
            if(dispAlert.value= true){
              state.dispAlertModal = true
            }
          })
        },

        async getLatestRecordState({state}) {
          await axios.get("/api/record")
          .then(res =>{
	    state.dispAlertModal = false
            state.latestRecordState = res.data.latestRecord
            state.latestRecordMenus = res.data.latestRecord
          })
          .catch((err) => {
            const {dispAlert} = useNotLoginedRedirect(err);
            if(dispAlert.value= true){
              state.dispAlertModal = true
            }
          })
        }
    }
})

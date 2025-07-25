
import { takeEvery, fork, put, all, call } from "redux-saga/effects";

// Login Redux States
import { FORGET_PASSWORD } from "./actionTypes";
import { userForgetPasswordSuccess, userForgetPasswordError } from "./actions";

//Include Both Helper File with needed methods
import { getFirebaseBackend } from "../../../helpers/firebase_helper";
import { postFakeForgetPwd, postJwtForgetPwd,} from "../../../helpers/fakebackend_helper";



//If user is send successfully send mail link then dispatch redux action's are directly from here.
function* forgetUser({ payload: { user, history } }) {
  try {
     if (process.env.REACT_APP_DEFAULTAUTH === "jwt") {
      //console.log("yield call öncesi"); 
      const response = yield call(postJwtForgetPwd, {email: user.email});
      //console.log("yield call sonrası"); 
       //yelki password reset test burasi
       if (response) {
       //console.log("saga.js satır 20:", response, "Türü:", typeof response);
       // Sayıyı önce string'e çevir-SİNAN
       const responseString = String(response);
       //console.log("String'e çevrildi:", responseString, "Türü:", typeof responseString);
       if (responseString.includes("NO")) {
        //console.log("NO var");
         yield put(userForgetPasswordError("This email address is not registered !"));
       } else {
         yield put(userForgetPasswordSuccess("Reset link is sent to your mailbox, check there first"));
       }
      }
    } else if (process.env.REACT_APP_API_URL) {
      const response = yield call(postFakeForgetPwd, user);
      
      if (response) {
        yield put(
          userForgetPasswordSuccess("Reset link are sent to your mailbox, check there first")
        );
      }
    }
  } catch (error) {
      //console.log("saga da Error:", error);
    if (error === "Internal Server Error") {
      //console.log("NO EKLE Internal Server Error");
      yield put(userForgetPasswordError("This email address is not registered ! or Internal Server Error"));
    } else{
       yield put(userForgetPasswordError(error));
    }
   
  }
}

export function* watchUserPasswordForget() {
  yield takeEvery(FORGET_PASSWORD, forgetUser);
}

function* forgetPasswordSaga() {
  yield all([fork(watchUserPasswordForget)]);
}


export default forgetPasswordSaga;

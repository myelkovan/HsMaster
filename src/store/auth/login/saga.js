import { call, put, takeEvery, takeLatest } from "redux-saga/effects";

// Login Redux States
import { LOGIN_USER, LOGOUT_USER, SOCIAL_LOGIN } from "./actionTypes";
import { apiError, loginSuccess, logoutUserSuccess } from "./actions";


//Include Both Helper File with needed methods
import { getFirebaseBackend } from "../../../helpers/firebase_helper";
import {
  postFakeLogin,
  postJwtLogin,
  postSocialLogin,
} from "../../../helpers/fakebackend_helper";

const fireBaseBackend = getFirebaseBackend();

function* loginUser({ payload: { user, history } }) {

  try {
    if (process.env.REACT_APP_DEFAULTAUTH === "jwt") {
      const response = yield call(postJwtLogin, {email: user.email, password: user.password});


      if (response) {
          if (response.includes("NO")) {
               yield put(apiError('Please check your email and password!'))
           }else if (response.includes('NA')) {
               yield put(apiError("Please activate your account by clicking the link emailed you!. Please don't forget to check spam or junk folder"))

           }else{
              if (Array.isArray(response) == false) 
                alert(response)
              else {
                //Kullanicinin sadece adini al ve bas harfini buyut
                   var ls_name = response[0].name.substr(0, response[0].name.indexOf(" "))
                   if (ls_name ===""){
                        ls_name = response[0].name
                   }

                   response[0].alias = ls_name.slice(0,1).toUpperCase() + ls_name.slice(1, ls_name.length)

                //alert("login oldu  " + response[0]); // SİNAN   mükerrer login logu kontrol etmek için
                //login oldu
                user = JSON.stringify(response[0])
                sessionStorage.setItem("authUser", user);
                
               
              // Ensure Dashly is available
              // if (window.dashly) {
              //     window.dashly.identify({
              //         name: response[0].alias,
              //         phone: response[0].mobile,
              //         email: response[0].email,
              //     });
              // } else {
              //     console.warn("Dashly is not initialized");
              // }


                yield put(loginSuccess(response));
                history('/dashboard');
                }
              }
      } else {
         yield put(apiError(response));
      }
    } else if (process.env.REACT_APP_API_URL) {
      const response = yield call(postFakeLogin, {
        email: user.email,
        password: user.password,
      });
      if (response.status === "success") {
        yield put(loginSuccess(response));
        history('/dashboard');
        sessionStorage.setItem("authUser", JSON.stringify(response));
      } else {
        yield put(apiError(response));
      }
    }
  } catch (error) {
    yield put(apiError(error));
  }
}

function* logoutUser() {
  try {
    sessionStorage.removeItem("authUser");
    if (process.env.REACT_APP_DEFAULTAUTH === "firebase") {
      const response = yield call(fireBaseBackend.logout);
      yield put(logoutUserSuccess(LOGOUT_USER, response));
    } else {
      yield put(logoutUserSuccess(LOGOUT_USER, true));
    }
  } catch (error) {
    yield put(apiError(LOGOUT_USER, error));
  }
}

function* socialLogin({ payload: { data, history, type } }) {
  try {
    if (process.env.REACT_APP_DEFAULTAUTH === "firebase") {
      const fireBaseBackend = getFirebaseBackend();
      const response = yield call(
        fireBaseBackend.socialLoginUser,
        data,
        type,
      );
      sessionStorage.setItem("authUser", JSON.stringify(response));
      yield put(loginSuccess(response));
    } else {
      const response = yield call(postSocialLogin, data);
      sessionStorage.setItem("authUser", JSON.stringify(response));
      yield put(loginSuccess(response));
    }
    history('/dashboard');
  } catch (error) {
    yield put(apiError(error));
  }
}

function* authSaga() {
  yield takeEvery(LOGIN_USER, loginUser);
  yield takeLatest(SOCIAL_LOGIN, socialLogin);
  yield takeEvery(LOGOUT_USER, logoutUser);
}

export default authSaga;

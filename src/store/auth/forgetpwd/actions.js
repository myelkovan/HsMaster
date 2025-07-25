import {
  FORGET_PASSWORD,
  FORGET_PASSWORD_SUCCESS,
  FORGET_PASSWORD_ERROR,
} from "./actionTypes";

export const userForgetPassword = (user, history, props) => {
  return {
    type: FORGET_PASSWORD,
    payload: { user, history, props },
  };
};

export const userForgetPasswordSuccess = message => {
  return {
    type: FORGET_PASSWORD_SUCCESS,
    payload: message,
  };
};

export const userForgetPasswordError = message => {
  return {
    type: FORGET_PASSWORD_ERROR,
    payload: message,
  };
};

//SİNAN 
export const resetForgetPasswordState = () => ({
  type: "RESET_FORGET_PASSWORD_STATE",
});
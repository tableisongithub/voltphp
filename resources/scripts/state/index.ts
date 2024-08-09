import { createStore } from "easy-peasy";
import UserContext, { UserStore } from "./user";



export interface ApplicationStore {
    UserContext: UserStore
}

const state: ApplicationStore = {
    UserContext
};

export const store = createStore(state);

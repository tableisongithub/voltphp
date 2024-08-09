import { Action, action } from 'easy-peasy';

export interface UserData {
    id: number,
    email: string,
    username: string
}

export interface UserStore {
    data?: UserData
    setUserData: Action<UserStore, UserData>;
    updateUserData: Action<UserStore, Partial<UserData>>;
}

const UserContext: UserStore = {
    data: undefined,
    setUserData: action((state, payload) => {
        state.data = payload;
    }),
    updateUserData: action((state, payload) => {
        // @ts-expect-error typescript detect this as error but its not
        state.data = { ...state.data, ...payload };
    })
};

export default UserContext
import {Navigate, Outlet} from "react-router";
import classes from "./Auth.module.scss";
import {useGetMe} from "../../../queries/useGetMe.ts";
import {PoweredByFooter} from "../../common/PoweredByFooter";
import {LanguageSwitcher} from "../../common/LanguageSwitcher";
import {isHiEvents} from "../../../utilites/helpers.ts";
import {BrandMark} from "../../common/BrandMark";

const AuthLayout = () => {
    const me = useGetMe();

    if (me.isSuccess) {
        return <Navigate to={'/manage/events'} />
    }

    return (
        <div className={classes.authLayout}>
            <main className={classes.container}>
                <div className={classes.logo}>
                    <BrandMark variant="light"/>
                </div>
                <div className={classes.wrapper}>
                    <Outlet />
                    {/*
                     * (c) Hi.Events Ltd 2025
                     *
                     * Hi.Events is licensed under the GNU Affero General Public License (AGPL) version 3.
                     * Retain the "Powered by Hi.Events" notice under Section 7(b).
                     * Full licence: https://github.com/HiEventsDev/hi.events/blob/main/LICENCE
                     */}
                    {!isHiEvents() && <PoweredByFooter />}
                    <div className={classes.languageSwitcher}>
                        <LanguageSwitcher />
                    </div>
                </div>
            </main>
        </div>
    );
};

export default AuthLayout;

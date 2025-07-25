import React, {useState, useEffect} from 'react';
import { useDeposit } from './DepositContext'; // Import the custom hook
import DepositModal from "../pages/DepositModal";


const DepositDropdown = () => {
    const { depositAmount } = useDeposit(); // Destructure depositAmount and updateDeposit from context
    const [depositModal, setDepositModal] = useState(false)
    const [user, setuser] = useState(null);

    useEffect(() => {
        const authuser = sessionStorage.getItem("authUser")
        const user = JSON.parse(authuser);
        setuser(user)
    }, []);  



    return (
       
        <header>
            <div>
            <React.Fragment>
            {user && user.permission_level === "0" && (
                <>
                    <div className="input-group">
                        <span id="depositamount"  className="input-group-text" id="hscode-addon-2" style={{ backgroundColor: '#68c468', }}
                        onClick={() => setDepositModal(true)} >${depositAmount}</span>
                    </div>

                    {depositModal &&
                        <DepositModal
                                depositModal={depositModal}
                                setDepositModal={setDepositModal}
                                user={user}
                        />
                    }
                </>
            )}
           </React.Fragment>
  
            </div>
        </header>

    );
};

export default DepositDropdown
import React, { useEffect} from "react";
import { useNavigate } from 'react-router-dom';
import { useDeposit } from './DepositContext';



export default function DefaultPage() {
    const navigate = useNavigate();
    const { refreshDeposit } = useDeposit();  

    useEffect(() => {
          const authuser = sessionStorage.getItem("authUser")
          const user = JSON.parse(authuser);

          //customer ise wizard penceresini ac  admin ise listeyi
          if (user.permission_level === '0' ){
            
                 navigate('/Dashboard1')
                 refreshDeposit()
          }else{
                navigate("/Dashboard1")
          }
   }, []);



return (
<div>
</div>

)
}



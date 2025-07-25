import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";



const Navdata = () => {
    const history = useNavigate();
    // Pages
    const [isProfile, setIsProfile] = useState(false);
    const [isMail, setIsMail] = useState(false);
    const [currentState, setCurrentState] = useState('orders');
    const [permission_level, setPermission_level] = useState(0);
    const [mail, setMail] = useState(false);
 


    function updateIconSidebar(e) {
        if (e && e.target && e.target.getAttribute("subitems")) {
            const ul = document.getElementById("two-column-menu");
            const iconItems = ul.querySelectorAll(".nav-icon.active");
            let activeIconItems = [...iconItems];
            activeIconItems.forEach((item) => {
                item.classList.remove("active");
                var id = item.getAttribute("subitems");
                if (document.getElementById(id))
                    document.getElementById(id).classList.remove("show");
            });
        }
    }



   
      


  useEffect(() => {
        const authuser = sessionStorage.getItem("authUser")
        const user = JSON.parse(authuser);
        setPermission_level(user.permission_level)
  }, []);
  




    useEffect(() => {
        document.body.classList.remove('twocolumn-panel');
       
        if (currentState !== 'Profile') {
            setIsProfile(false);
        }
        if (currentState !== 'Mail') {
            setIsMail(false);
        }


    }, [
        history,
        currentState,
        isProfile,
        isMail

    ]);



    //Menu burada olusuyor
    var menuItems = []
    
    //CUSTOMER
    if(permission_level == 0){
        menuItems = [
                {
                    label: "Menu",
                    isHeader: true,
                },
                
                {
                    id: "mail",
                    label: "Get Support",
                    icon: "ri-mail-line",
                    link: "/Mail",
                    stateVariables: isMail,
                    click: function (e) {
                        e.preventDefault();
                        setIsMail(!isMail);
                        setCurrentState("Mail");
                        updateIconSidebar(e);
                    },
                }
        ]
    }



     



    return <React.Fragment>{menuItems}</React.Fragment>;
};
export default Navdata;

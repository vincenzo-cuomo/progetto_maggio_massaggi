import styles from "./errorcomponent.module.css"
import sadFace from "../../../public/Images/sad.png"

export default function ErrorComponent(){
    return (
        <div className={styles.errorMessage}> 
            Non è stato possibile ricavare i massaggi, riprovare più tardi
            <img src={sadFace} style={{ width: "clamp(30px, 30%, 200px)", backgroundColor: "white", borderRadius: "50%", objectFit: "cover", marginLeft: "1%" }}></img>
        </div>
    )
}
import React from "react"
import { VectorMap } from "react-jvectormap"
import "./jquery-jvectormap.scss"

const MapVector = (props) => {
  const map = React.createRef(null)
  return(
    <>
    <div style={{ width: props.width, height: 500 }}>
        <VectorMap
          map={props.value}
          backgroundColor="transparent"
          ref={map}
          containerStyle={{
            width: "100%",
            height: "80%",
          }}
          regionStyle={{
            initial: {
              fill: props.color,
              stroke: "none",
              "stroke-width": 0,
              "stroke-opacity": 0,
            },
            hover: {
              "fill-opacity": 0.8,
              cursor: "pointer",
            },
            selected: {
              fill: "#2938bc", //what colour clicked country will be
            },
            markers:{
             US: { latLng: [props.point_lat, props.point_lng], name: props.point_city }
            },
            selectedHover: {},
          }}
          containerClassName="map"
        />
      </div>
    </>
  )
}

export default MapVector;
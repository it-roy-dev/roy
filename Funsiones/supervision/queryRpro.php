<?php
  function VMSE($sbs,$emp,$t,$yk){
    $resultado = [0,0];
    $query = "


	SELECT 
	sum(META) META, 
	SUM(VENTA) VENTA            
FROM(
select	   
					A.COD_VENDEDOR CODIGO, 
					A.VENDEDOR NOMBRE,
					PUESTO,
					NVL(META,0)META,
					ROUND(SUM(A.venta_SIN_IVA),2) VENTA,
					NVL(ROUND(SUM(A.venta_SIN_IVA) - (META),2),0) DIFERENCIA,
					SUM(A.TRANSACCIONES) FACTURAS,
					NVL(SUM(A.PAR_ROY),0) ROY,
					NVL(SUM(A.PAR_OTROS),0) OTROS,
					NVL(SUM(A.PAR_ROY),0) + NVL(SUM(A.PAR_OTROS),0)  PARES,
					NVL(SUM(A.PAR_ACCE),0) ACCESORIOS,
					ROUND(DECODE(SUM(A.CANTIDAD),0,SUM(A.VENTA_SIN_IVA),(SUM(A.VENTA_SIN_IVA) / SUM(A.CANTIDAD))),2) PPP,
					ROUND(DECODE(SUM(A.TRANSACCIONES),0,SUM(A.CANTIDAD),(SUM(A.CANTIDAD) / SUM(A.TRANSACCIONES))),2)UPT, 
					ROUND(DECODE(SUM(A.TRANSACCIONES),0,SUM(A.VENTA_SIN_IVA),(SUM(A.VENTA_SIN_IVA) / SUM(A.TRANSACCIONES))),2) QPT,
					  NVL(ROUND(SUM(A.venta_SIN_IVA /HORA),2),0) VH,
					CONTRATACION,
					 HORA
					FROM (
					select  t1.store_code, trunc(t1.created_datetime) FECHA, t1.employee1_login_name COD_VENDEDOR,
				   A.META,A.HORA,
					t1.employee1_full_name VENDEDOR,
					E.FECHA_INGRESO CONTRATACION,
					E.PUESTO,
					case when t1.receipt_type=0 then 1 when t1.receipt_type=1 then -1 end TRANSACCIONES, 
					
					sum(case when t1.receipt_type=0 and t2.vend_code='001' then (t2.qty)
							 when t1.receipt_type=1 and t2.vend_code='001' then (t2.qty)*-1 end) as par_roy, 
					
					sum(case when t1.receipt_type=0 and t2.vend_code <> 001 and SUBSTR(T2.DCS_CODE,1,3)not in ('ACC','SER','PRE','PRO')  then (t2.qty)
							 when t1.receipt_type=1 and t2.vend_code <> 001 and SUBSTR(T2.DCS_CODE,1,3)not in ('ACC','SER','PRE','PRO')  then (t2.qty)*-1 end) as par_otros, 
					
					sum(case when t1.receipt_type=0 and   SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then (t2.qty)
							 when t1.receipt_type=1 and   SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then (t2.qty)*-1 end) par_acce,
							 
					sum(case when t1.receipt_type=0  and SUBSTR(T2.DCS_CODE,1,3)not in ('SER','PRE','PRO')   then (T2.qty) 
							 when t1.receipt_type=1  and SUBSTR(T2.DCS_CODE,1,3)not in ('SER','PRE','PRO')  then (T2.qty)*-1 end ) as cantidad,           
					
					sum(case when t1.receipt_type=0 and SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then (t2.qty*T2.PRICE)
							 when t1.receipt_type=1 and SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then (t2.qty*T2.PRICE)*-1 end)venta_CON_IVA_ACC,
					
					 sum(case when t1.receipt_type=0 and   SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then ((T2.price)/1.12*(T2.qty)) 
							  when t1.receipt_type=1 and   SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then ((T2.price)/1.12*(T2.qty))*-1 end ) as venta_sin_iva_ACC,    
					
					sum(case when t1.receipt_type=0 then (t2.qty*t2.cost) when t1.receipt_type=1 then (t2.qty*t2.cost)*-1 else 0 end) as costo, 
					
					sum(case WHEN t1.receipt_type=0 AND SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then ((T2.COST)*(T2.qty))
							 when t1.receipt_type=1 AND SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then ((T2.COST)*(T2.qty))*-1 end ) as COSTO_sin_iva_ACC ,
						  
					NVL(sum(case when t1.receipt_type=0 then ((t2.price-( t2.price*NVL(t1.disc_perc,0)/100))*(t2.qty))
								 when t1.receipt_type=1 then ((t2.price-( t2.price*NVL(t1.disc_perc,0)/100))*(t2.qty))*-1 end ),0) as venta_con_iva, 
						  
					NVL(sum(case when t1.receipt_type=0 then ((t2.price-( t2.price*NVL(t1.disc_perc,0)/100))*(t2.qty))/1.12 
								 when t1.receipt_type=1 then ((t2.price-( t2.price*NVL(t1.disc_perc,0)/100))*(t2.qty))/1.12*-1 end ),0) as venta_sin_iva   					   
						  
					from rps.document t1 
					inner join rps.document_item t2 on (t1.sid = t2.doc_sid)
					LEFT JOIN ROY_META_SEM_X_VENDEDOR A ON  TO_CHAR(trunc(T1.CREATED_DATETIME,'d'),'IW')+1 = A.SEMANA AND TO_CHAR(T1.CREATED_DATETIME,'IYYY') = A.ANIO AND T1.STORE_NO = A.TIENDA AND t1.employee1_login_name = A.CODIGO_EMPLEADO AND T1.SBS_NO = A.SBS
					left join ROY_VENDEDORES_FRIED E on (E.CODIGO_VENDEDOR = t1.employee1_login_name)
					
					where 1=1
					and t1.status=4 
				 and t1.employee1_full_name not in ('SYSADMIN')
					 and t1.receipt_type<>2
					   AND T1.sbs_no = $sbs
					   AND t1.STORE_NO in($t)
					   and t1.employee1_login_name = '$emp'
					   and EXTRACT(YEAR FROM t1.CREATED_dATETIME)|| TO_CHAR(trunc(T1.CREATED_DATETIME,'d'),'IW')+1 = '$yk'
				  --   and t1.CREATED_DATETIME between to_date('2024-05-26 00:00:00', 'YYYY-MM-DD HH24:MI:SS') ANd to_date('2024-06-01 23:59:59', 'YYYY-MM-DD HH24:MI:SS')
					 
					 group by t1.store_code,  t1.employee1_login_name, t1.employee1_full_name, trunc(t1.created_datetime), T1.DOC_NO, t1.receipt_type, t1.disc_amt,  A.META, A.HORA,E.PUESTO,E.FECHA_INGRESO
						   
			  )A 
						 GROUP BY A.STORE_CODE, A.COD_VENDEDOR, A.VENDEDOR, META,  HORA, PUESTO, CONTRATACION
						  ORDER BY DECODE(PUESTO, 'JEFE DE TIENDA', 1, 'SUB JEFE DE TIENDA', 2, 'ASESOR DE VENTAS', 3, 4))";

    $res = consultaOracle(1,$query);
    if($res == ""){
      return $resultado;
    }
    else {
      return $res;
    }
  }

  function VMST($sbs, $t, $yk)
  {
    $query = "
   SELECT 
	sum(META) META, 
	SUM(VENTA) VENTA            
FROM(
select	   
					A.COD_VENDEDOR CODIGO, 
					A.VENDEDOR NOMBRE,
					PUESTO,
					NVL(META,0)META,
					ROUND(SUM(A.venta_SIN_IVA),2) VENTA,
					NVL(ROUND(SUM(A.venta_SIN_IVA) - (META),2),0) DIFERENCIA,
					SUM(A.TRANSACCIONES) FACTURAS,
					NVL(SUM(A.PAR_ROY),0) ROY,
					NVL(SUM(A.PAR_OTROS),0) OTROS,
					NVL(SUM(A.PAR_ROY),0) + NVL(SUM(A.PAR_OTROS),0)  PARES,
					NVL(SUM(A.PAR_ACCE),0) ACCESORIOS,
					ROUND(DECODE(SUM(A.CANTIDAD),0,SUM(A.VENTA_SIN_IVA),(SUM(A.VENTA_SIN_IVA) / SUM(A.CANTIDAD))),2) PPP,
					ROUND(DECODE(SUM(A.TRANSACCIONES),0,SUM(A.CANTIDAD),(SUM(A.CANTIDAD) / SUM(A.TRANSACCIONES))),2)UPT, 
					ROUND(DECODE(SUM(A.TRANSACCIONES),0,SUM(A.VENTA_SIN_IVA),(SUM(A.VENTA_SIN_IVA) / SUM(A.TRANSACCIONES))),2) QPT,
					  NVL(ROUND(SUM(A.venta_SIN_IVA /HORA),2),0) VH,
					CONTRATACION,
					 HORA
					FROM (
					select  t1.store_code, trunc(t1.created_datetime) FECHA, t1.employee1_login_name COD_VENDEDOR,
				   A.META,A.HORA,
					t1.employee1_full_name VENDEDOR,
					E.FECHA_INGRESO CONTRATACION,
					E.PUESTO,
					case when t1.receipt_type=0 then 1 when t1.receipt_type=1 then -1 end TRANSACCIONES, 
					
					sum(case when t1.receipt_type=0 and t2.vend_code='001' then (t2.qty)
							 when t1.receipt_type=1 and t2.vend_code='001' then (t2.qty)*-1 end) as par_roy, 
					
					sum(case when t1.receipt_type=0 and t2.vend_code <> 001 and SUBSTR(T2.DCS_CODE,1,3)not in ('ACC','SER','PRE','PRO')  then (t2.qty)
							 when t1.receipt_type=1 and t2.vend_code <> 001 and SUBSTR(T2.DCS_CODE,1,3)not in ('ACC','SER','PRE','PRO')  then (t2.qty)*-1 end) as par_otros, 
					
					sum(case when t1.receipt_type=0 and   SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then (t2.qty)
							 when t1.receipt_type=1 and   SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then (t2.qty)*-1 end) par_acce,
							 
					sum(case when t1.receipt_type=0  and SUBSTR(T2.DCS_CODE,1,3)not in ('SER','PRE','PRO')   then (T2.qty) 
							 when t1.receipt_type=1  and SUBSTR(T2.DCS_CODE,1,3)not in ('SER','PRE','PRO')  then (T2.qty)*-1 end ) as cantidad,           
					
					sum(case when t1.receipt_type=0 and SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then (t2.qty*T2.PRICE)
							 when t1.receipt_type=1 and SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then (t2.qty*T2.PRICE)*-1 end)venta_CON_IVA_ACC,
					
					 sum(case when t1.receipt_type=0 and   SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then ((T2.price)/1.12*(T2.qty)) 
							  when t1.receipt_type=1 and   SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then ((T2.price)/1.12*(T2.qty))*-1 end ) as venta_sin_iva_ACC,    
					
					sum(case when t1.receipt_type=0 then (t2.qty*t2.cost) when t1.receipt_type=1 then (t2.qty*t2.cost)*-1 else 0 end) as costo, 
					
					sum(case WHEN t1.receipt_type=0 AND SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then ((T2.COST)*(T2.qty))
							 when t1.receipt_type=1 AND SUBSTR(T2.DCS_CODE,1,3)= 'ACC' then ((T2.COST)*(T2.qty))*-1 end ) as COSTO_sin_iva_ACC ,
						  
					NVL(sum(case when t1.receipt_type=0 then ((t2.price-( t2.price*NVL(t1.disc_perc,0)/100))*(t2.qty))
								 when t1.receipt_type=1 then ((t2.price-( t2.price*NVL(t1.disc_perc,0)/100))*(t2.qty))*-1 end ),0) as venta_con_iva, 
						  
					NVL(sum(case when t1.receipt_type=0 then ((t2.price-( t2.price*NVL(t1.disc_perc,0)/100))*(t2.qty))/1.12 
								 when t1.receipt_type=1 then ((t2.price-( t2.price*NVL(t1.disc_perc,0)/100))*(t2.qty))/1.12*-1 end ),0) as venta_sin_iva   					   
						  
					from rps.document t1 
					inner join rps.document_item t2 on (t1.sid = t2.doc_sid)
					LEFT JOIN ROY_META_SEM_X_VENDEDOR A ON  TO_CHAR(trunc(T1.CREATED_DATETIME,'d'),'IW')+1 = A.SEMANA AND TO_CHAR(T1.CREATED_DATETIME,'IYYY') = A.ANIO AND T1.STORE_NO = A.TIENDA AND t1.employee1_login_name = A.CODIGO_EMPLEADO AND T1.SBS_NO = A.SBS
					left join ROY_VENDEDORES_FRIED E on (E.CODIGO_VENDEDOR = t1.employee1_login_name)
					
					where 1=1
					and t1.status=4 
				 and t1.employee1_full_name not in ('SYSADMIN')
					 and t1.receipt_type<>2
					   AND T1.sbs_no = $sbs
					   AND t1.STORE_NO in($t)
					
					   and EXTRACT(YEAR FROM t1.CREATED_dATETIME)|| TO_CHAR(trunc(T1.CREATED_DATETIME,'d'),'IW')+1 = '$yk'
				  --   and t1.CREATED_DATETIME between to_date('2024-05-26 00:00:00', 'YYYY-MM-DD HH24:MI:SS') ANd to_date('2024-06-01 23:59:59', 'YYYY-MM-DD HH24:MI:SS')
					 
					 group by t1.store_code,  t1.employee1_login_name, t1.employee1_full_name, trunc(t1.created_datetime), T1.DOC_NO, t1.receipt_type, t1.disc_amt,  A.META, A.HORA,E.PUESTO,E.FECHA_INGRESO
						   
			  )A 
						 GROUP BY A.STORE_CODE, A.COD_VENDEDOR, A.VENDEDOR, META,  HORA, PUESTO, CONTRATACION
						  ORDER BY DECODE(PUESTO, 'JEFE DE TIENDA', 1, 'SUB JEFE DE TIENDA', 2, 'ASESOR DE VENTAS', 3, 4))";


    return consultaOracle(1, $query);
  }

  function MTS ($t,$s,$a,$sbs){
    $res = [0];

    $query = "SELECT ROUND(SUM(META_S_IVA),2) META FROM ROY_META_DIARIA_TDS
              WHERE TIENDA = $t
              AND SEMANA = $s
              AND EXTRACT(YEAR FROM FECHA) =$a 
              ";

    $resultado = consultaOracle(1, $query);
    if(!$resultado){
      return $res;
    }
    else{
      return $resultado;
    }
  }

  function MTSS ($t,$s,$a,$sbs){
    $res = [0];

    $query = "SELECT ROUND(SUM(META),2) META FROM ROY_META_SEM_TDS M
                            INNER JOIN RPS.STORE S ON M.TIENDA = S.STORE_NO 
                            INNER JOIN  rps.subsidiary SB on s.sbs_sid=sB.sid AND M.SBS = SB.SBS_NO 
                            WHERE SB.SBS_NO = $sbs
                            AND S.UDF1_STRING = $t
              AND M.SEMANA = $s
              AND M.ANIO =$a
              ";

    $resultado = consultaOracle(1, $query);
    if(!$resultado){
      return $res;
    }
    else{
      return $resultado;
    }
  }
select 
    R.*,
    R.peso * R.resultado as resultado_ponderado,
    (case when (select resul.ID_APPROLE from R_VALRUOLO resul where resul.ID_VALUTPREST=R.ID_VALUTPREST and resul.IND_PRINC='Y')='874416589' then 180 else 360 end) as modelo_evaluacion
from 
(
    select
        PERSONA.CSF_CFSPERSONA as cedula,
        EVALUADOR.DES_NOMEPERS as evaluador_nombres,
        EVALUADOR.DES_COGNOMEPERS as evaluador_primer_apellido,
        EVALUADOR.DES_SECCOGNOME as evaluador_segundo_apellido,
        EVALUADOR.CSF_CFSPERSONA as evaluador_cedula,
        ROL_EVALUADOR.DES_APPROLE as rol_evaluador,
        ROL.DES_RUOLO as rol_evaluado,
        COMPETENCIA.DES_REQUISITO as competencia,
        (
            case 
                when ROL_EVALUADOR.DES_APPROLE='Colaborador' then 0.35
                when ROL_EVALUADOR.DES_APPROLE='Coach' then 0.75
                when ROL_EVALUADOR.DES_APPROLE='Coach 360' then 0.4
            else
                0.25
            end
        ) as peso,
        COMPETENCIA_N.COD_VALORESCALA as resultado,
        YEAR(RESULTADO.DTA_INIZIO) as ano,
        RESULTADO.PRG_RIGA as prg_riga,
        RESULTADO.ID_VALUTPREST as ID_VALUTPREST

    from 
        R_VALRUOLO RESULTADO left join ANAGPERS PERSONA on RESULTADO.ID_PERSONA=PERSONA.ID_PERSONA
        left join ANAGPERS EVALUADOR on RESULTADO.ID_VALUTATORE=EVALUADOR.ID_PERSONA
        left join RIGAVALUT COMPETENCIA_N on COMPETENCIA_N.ID_VALUTPREST=RESULTADO.ID_VALUTPREST and RESULTADO.PRG_RIGA=COMPETENCIA_N.PRG_RIGA
        left join TB_REQUI COMPETENCIA on COMPETENCIA_N.COD_REQUISITO=COMPETENCIA.COD_REQUISITO and COMPETENCIA_N.COD_TIPOREQ=COMPETENCIA.COD_TIPOREQ
        left join TB_APPROLE ROL_EVALUADOR on RESULTADO.ID_APPROLE=ROL_EVALUADOR.ID_APPROLE
        left join RUOLO ROL on RESULTADO.COD_RUOLO=ROL.COD_RUOLO
    where
        RESULTADO.COD_CLASSEV=18
) R
where R.resultado is not null 















select 
    R.*,
    R.peso * R.resultado as resultado_ponderado
from 
(
    select
        PERSONA.CSF_CFSPERSONA as cedula,
        EVALUADOR.DES_NOMEPERS as evaluador_nombres,
        EVALUADOR.DES_COGNOMEPERS as evaluador_primer_apellido,
        EVALUADOR.DES_SECCOGNOME as evaluador_segundo_apellido,
        EVALUADOR.CSF_CFSPERSONA as evaluador_cedula,
        ROL_EVALUADOR.DES_APPROLE as rol_evaluador,
        ROL.DES_RUOLO as rol_evaluado,
        (case when RESULTADO.ID_APPROLE='874416589' then 180 else 360 end) as modelo_evaluacion,
        COMPETENCIA.DES_REQUISITO as competencia,
        (
            case 
                when ROL_EVALUADOR.DES_APPROLE='Colaborador' then 0.35
                when ROL_EVALUADOR.DES_APPROLE='Coach' then 0.75
                when ROL_EVALUADOR.DES_APPROLE='Coach 360' then 0.4
            else
                0.25
            end
        ) as peso,
        COMPETENCIA_N.COD_VALORESCALA as resultado,
        YEAR(RESULTADO.DTA_INIZIO) as ano

    from 
        R_VALRUOLO RESULTADO left join ANAGPERS PERSONA on RESULTADO.ID_PERSONA=PERSONA.ID_PERSONA
        left join ANAGPERS EVALUADOR on RESULTADO.ID_VALUTATORE=EVALUADOR.ID_PERSONA
        left join RIGAVALUT COMPETENCIA_N on COMPETENCIA_N.ID_VALUTPREST=RESULTADO.ID_VALUTPREST and RESULTADO.PRG_RIGA=COMPETENCIA_N.PRG_RIGA
        left join TB_REQUI COMPETENCIA on COMPETENCIA_N.COD_REQUISITO=COMPETENCIA.COD_REQUISITO
        left join TB_APPROLE ROL_EVALUADOR on RESULTADO.ID_APPROLE=ROL_EVALUADOR.ID_APPROLE
        left join RUOLO ROL on RESULTADO.COD_RUOLO=ROL.COD_RUOLO
    where
        RESULTADO.COD_CLASSEV=18
) R
where R.resultado is not null 
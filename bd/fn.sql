DELIMITER $$
CREATE PROCEDURE `getResultadoConsolidadoIndicador`(IN `_idresultado` VARCHAR(256), IN `_indicador` INT, OUT `consolidado` FLOAT, IN `modelo_evaluacion` VARCHAR(3))
    MODIFIES SQL DATA
begin

declare suma_180 float;
declare suma_360 float;

set suma_180 = 0;
set suma_360 = 0;

create table suma_indicador (
	suma_autoevaluador float not null default 0.00,
	suma_coach float not null default 0.00,
	suma_coach_360 float not null default 0.00,
	suma_colaborador float not null default 0.00
)ENGINE=MEMORY;

insert into suma_indicador (suma_autoevaluador, suma_coach, suma_coach_360, suma_colaborador)
select 
    avg(resultado.autoevaluador) * 0.25 as suma_autoevaluador, 
    avg(resultado.coach) * 0.75 as suma_coach,
    avg(resultado.coach_360) * 0.4 as suma_coach_360,
    avg(resultado.colaborador) * 0.35 as suma_colaborador
from AR_Resultado_Indicador as resultado
where resultado.id_resultado=_idresultado and resultado.indicador=_indicador
group by concat(resultado.indicador, '_', resultado.id_resultado);


set suma_180 = (
	select (suma_coach + suma_autoevaluador)
    from suma_indicador
);

set suma_360 = (
	select (suma_coach_360 + suma_colaborador + suma_autoevaluador)
    from suma_indicador
);

drop table suma_indicador;

if modelo_evaluacion = '180' then
	set consolidado = suma_180;
else
	set consolidado = suma_360;
end if;

end$$
DELIMITER ;










DELIMITER $$
CREATE PROCEDURE `generarResultadosConsolidadosIndicadores`()
    MODIFIES SQL DATA
begin

declare consolidado float;
declare exit_loop boolean default false;
declare rid, indicador int;
declare modo_de_evaluacion, idresultado varchar(256);

declare curs cursor for select  
                    ri.id as riid,
                    ri.id_resultado as id_resultado, 
                    ri.indicador as indicador,
                    r.modo_de_evaluacion as modo_de_evaluacion
                from AR_Resultado_Indicador as ri, AR_Resultado as r
                where 
                    ri.resultado=r.id
                    and ri.id_resultado is not null
                group by concat(ri.id_resultado, ri.indicador);

create table Log (
	mensaje varchar(512),
    hora datetime
) engine=memory;

open curs;

_loop: loop
	fetch curs into rid, idresultado, indicador, modo_de_evaluacion;
    
    insert into Log (mensaje, hora)
    values (concat('Viendo registro RID=', rid), now());
    
    insert into Log (mensaje, hora)
    values (concat('Calculando consolidado'), now());
    
    call getResultadoConsolidadoIndicador(idresultado, indicador, @consolidado, modo_de_evaluacion);
    set consolidado = (select @consolidado);
    
    insert into Log (mensaje, hora)
    values (concat('Resultado de consolidado =', consolidado), now());
    
    update AR_Resultado_Indicador
    set resultado_consolidado=consolidado
    where id=rid;
    
    insert into Log (mensaje, hora)
    values (concat('Registro actualizado RID=', rid), now());
    
     IF exit_loop THEN
         CLOSE curs;
         LEAVE _loop;
     END IF;
end loop _loop;

drop table Log;

end$$
DELIMITER ;
export const AbgabeterminStatusLegende = {
	name: 'AbgabeterminStatusLegende',
	template: `	
		<div class="text-center">
			<div class="col"  style="width: 80%; margin-left: 12px;">
			
				<div class="row" style="margin-bottom: 2px">
					<div class="col-auto verspaetet-header" style="height: 36px; width:36px; padding: 0px; display: flex; align-items: center; justify-content: center;">
						<i class="fa-solid fa-triangle-exclamation"></i>
					</div>
					<div class="col-auto" style="display: flex; align-items: center;">
						<h5>{{ $capitalize($p.t('abgabetool/c4tooltipVerspaetet')) }}</h5>
					</div>
				</div>
				
				<div class="row" style="margin-bottom: 2px">
					<div class="col-auto verpasst-header" style="height: 36px; width:36px; padding: 0px; display: flex; align-items: center; justify-content: center;">
						<i class="fa-solid fa-calendar-xmark"></i>
					</div>
					<div class="col-auto" style="display: flex; align-items: center;">
						<h5>{{ $capitalize($p.t('abgabetool/c4tooltipVerpasst')) }}</h5>
					</div>
				</div>
				
				<div class="row" style="margin-bottom: 2px">
					<div class="col-auto abzugeben-header" style="height: 36px; width:36px; padding: 0px; display: flex; align-items: center; justify-content: center;">
						<i class="fa-solid fa-hourglass-half"></i>
					</div>
					<div class="col-auto" style="display: flex; align-items: center;">
						<h5>{{ $capitalize($p.t('abgabetool/c4tooltipAbzugeben')) }}</h5>
					</div>
				</div>
				
				<div class="row" style="margin-bottom: 2px">
					<div class="col-auto standard-header" style="height: 36px; width:36px; padding: 0px; display: flex; align-items: center; justify-content: center;">
						<i class="fa-solid fa-clock"></i>
					</div>
					<div class="col-auto" style="display: flex; align-items: center;">
						<h5>{{ $capitalize($p.t('abgabetool/c4tooltipStandard')) }}</h5>
					</div>
				</div>
				
				<div class="row" style="margin-bottom: 2px">
					<div class="col-auto abgegeben-header" style="height: 36px; width:36px; padding: 0px; display: flex; align-items: center; justify-content: center;">
						<i class="fa-solid fa-check"></i>
					</div>
					<div class="col-auto" style="display: flex; align-items: center;">
						<h5>{{ $capitalize($p.t('abgabetool/c4tooltipAbgegeben')) }}</h5>
					</div>
					
				</div>

			</div>
		</div>	
	`
};
export default AbgabeterminStatusLegende;
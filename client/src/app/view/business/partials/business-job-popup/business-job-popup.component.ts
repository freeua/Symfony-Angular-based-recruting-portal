import {Component, EventEmitter, forwardRef, Inject, Input, OnInit, Output} from '@angular/core';
import { SharedService } from '../../../../services/shared.service';
import { BusinessService } from '../../../../services/business.service';
import { IBusinessJobFullDetails } from '../../../../../entities/models';
// import { BusinessCurrentJobsComponent } from '../business-current-jobs/business-current-jobs.component';
import { ToastrService } from 'ngx-toastr';
import set = Reflect.set;
import { BusinessOldJobsComponent } from '../business-old-jobs/business-old-jobs.component';

@Component({
  selector: 'app-business-job-popup',
  templateUrl: './business-job-popup.component.html',
  styleUrls: ['./business-job-popup.component.scss']
})
export class BusinessJobPopupComponent implements OnInit {
  public _currentBusinessJobId: number;
  @Input() closePopup;
  @Input('currentBusinessJobId') set currentBusinessJobId(currentBusinessJobId: number) {
    if (currentBusinessJobId) {
      this._currentBusinessJobId = currentBusinessJobId;
      this.getSpecificBusinessJob(currentBusinessJobId);
    }
  }
  get currentBusinessJobId(): number {
    return this.currentBusinessJobId;
  }

  @Input() businessJobs = Array();
  @Output() deleteJob:  EventEmitter<any> = new EventEmitter();
  @Output() closeJob:  EventEmitter<any> = new EventEmitter();
  public currentBusinessJob: IBusinessJobFullDetails;
  public jobAvailability: string;
  public jobVideo: string;
  public nationality: string;
  public qualification: string;
  public articlesFirms: string;
  public daysBeforeClosure: number;
  public location: string;
  public loaderPopup = true;
  public checkDate: number;

  constructor(
      private readonly _sharedService: SharedService,
      private readonly _businessService: BusinessService,
      private readonly _toastr: ToastrService
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {

    /*emit.funcName.emit()*/
  }

  /**
   * fetches specified business job by id
   * @param id
   * @returns void
   */
  public getSpecificBusinessJob(id: number): void {
    this._businessService.getBusinessJobById(id)
      .then((response) => {
        this.currentBusinessJob = response;
        this.jobAvailability = this._sharedService.getAvailabilityInHumanReadableForm(this.currentBusinessJob.availability);
        this.jobVideo = (this.currentBusinessJob) ? 'Yes' : 'No';
        this.nationality = this._sharedService.getNationalityInHumanReadableForm(this.currentBusinessJob.nationality);
        this.qualification = this._sharedService.getQualificationInHumanReadableForm(this.currentBusinessJob.qualification);
        this.articlesFirms = this.currentBusinessJob.articlesFirm.join(', ');
        this.daysBeforeClosure = this._sharedService.getDifferenceInDays(
            new Date(), this.currentBusinessJob.closureDate);

        this.checkDate = Math.sign(this.daysBeforeClosure);

        setTimeout(() => {
          this.loaderPopup = false;
        }, 1000);
      })
      .catch((error) => { this._sharedService.showRequestErrors(error); });
  }

    /**
     * closes business job specified with id
     * @param job
     * @returns void
     */
  public async closeBusinessJob(job): Promise<void> {
    try{
      await this._businessService.closeBusinessJob(job.id, { status: false });
      this.closeJob.emit(job);
      if (job.approve === true) {
        this._sharedService.sidebarBusinessBadges.jobApproved--;
        this._sharedService.sidebarBusinessBadges.jobOld++;
      } else {
        this._sharedService.sidebarBusinessBadges.jobAwaiting--;
        this._sharedService.sidebarBusinessBadges.jobOld++;
      }

      this._toastr.success('Job has been successfully closed!');
      this.closePopup();
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

    /**
     * deletes business job specified with id
     * @param job
     * @param status
     * @returns void
     */
  public async deleteBusinessJob(job, status): Promise<void> {

    try {
      await this._businessService.deleteBusinessJob(job.id);
      this.deleteJob.emit(job);
      this.closePopup();

      if(status === true){
        if (job.approve === true) {
          this._sharedService.sidebarBusinessBadges.jobApproved--;
        } else {
          this._sharedService.sidebarBusinessBadges.jobAwaiting--;
        }
      }
      else {
        this._sharedService.sidebarBusinessBadges.jobOld--;
      }
      this._toastr.success('Job has been successfully closed!');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }
}

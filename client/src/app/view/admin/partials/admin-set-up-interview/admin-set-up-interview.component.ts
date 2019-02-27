import { Component, OnInit } from '@angular/core';
import { AdminService } from '../../../../services/admin.service';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../../services/shared.service';
import { AdminInterviewList} from '../../../../../entities/models-admin';
import { Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-set-up-interview',
  templateUrl: './admin-set-up-interview.component.html',
  styleUrls: ['./admin-set-up-interview.component.scss']
})
export class AdminSetUpInterviewComponent implements OnInit {

  public setUpInterviewListCandidate = Array<AdminInterviewList>();
  public setUpInterviewListClient = Array<AdminInterviewList>();

  public preloaderPage = true;
  public totalCount: number;

  public paginationLoaderCandidate = false;
  public paginationLoaderClient = false;
  public paginationCandidate = 1;
  public paginationClient = 1;
  public loadMoreCheckCandidate = true;
  public loadMoreCheckClient = true;

  public interviewCheck = false;
  public modalActiveClose: any;
  public selectedBusinessId;
  public selectedBusinessJob;

  constructor(
    private readonly _adminService: AdminService,
    private readonly _toastr: ToastrService,
    private readonly _sharedService: SharedService,
    private readonly _router: Router,
    private readonly _modalService: NgbModal
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.setUpInterviewListCandidate = [];
    this.setUpInterviewListClient = [];
    this.getSetUpInterviewsCandidate().then(() => {
      this.getSetUpInterviewsClient();
    });
  }

  /**
   * Router admin for candidate on id
   * @param id
   */
  public routeCandidate(id) {
    this._router.navigate(['/admin/edit_candidate'], { queryParams: { candidateId: id} });
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param id {number} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCentered(content: any,  id: number) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedBusinessId = id;
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param jobId {number} - job id to be used for fetching data and showing in popup
   * @param clientID {number} - job id to be used for fetching data and showing in popup
   * @param subcontent {any} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCenterJob(content: any, jobId, clientID, subcontent): void {
    if (jobId){
      this.selectedBusinessJob = {
        id: jobId
      };
      this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    } else {
      this.selectedBusinessId = clientID;
      this.modalActiveClose = this._modalService.open(subcontent, { centered: true, 'size': 'lg' });
    }
  }

  /**
   * Load pagination candidate
   */
  public async loadPaginationCandidate(): Promise<void> {

    this.paginationCandidate++;
    this.paginationLoaderCandidate = true;
    this.getSetUpInterviewsCandidate();
  }

  /**
   * Load pagination client
   */
  public async loadPaginationClient(): Promise<void> {

    this.paginationClient++;
    this.paginationLoaderClient = true;
    this.getSetUpInterviewsClient();
  }

  /**
   * Get set up interview
   * @return {Promise<void>}
   */
  public async getSetUpInterviewsCandidate(): Promise<void> {
    try {
      const response = await this._adminService.getSetUpInterviewsCandidate(this.paginationCandidate);
      response.items.forEach((item) => {
        item.enabled = false;
        this.setUpInterviewListCandidate.push(item);
      });

      if (response.pagination.total_count === this.setUpInterviewListCandidate.length) {
        this.loadMoreCheckCandidate = false;
      }
      else if (response.pagination.total_count !== this.setUpInterviewListCandidate.length){
        this.loadMoreCheckCandidate = true;
      }
      this.paginationLoaderCandidate = false;

      // this.totalCount = response.pagination.total_count;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get set up interview
   * @return {Promise<void>}
   */
  public async getSetUpInterviewsClient(): Promise<void> {
    try {
      const response = await this._adminService.getSetUpInterviewsClient(this.paginationClient);
      response.items.forEach((item) => {
        item.enabled = false;
        this.setUpInterviewListClient.push(item);
      });

      if (response.pagination.total_count === this.setUpInterviewListClient.length) {
        this.loadMoreCheckClient = false;
      }
      else if (response.pagination.total_count !== this.setUpInterviewListClient.length){
        this.loadMoreCheckClient = true;
      }
      this.paginationLoaderClient = false;

      // this.totalCount = response.pagination.total_count;
      this.preloaderPage = false;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set up interviews
   * @param interview {object}
   * @param listItems {Array}
   * @param enabled {boolean}
   * @return {Promise<void>}
   */
  public async adminSetUpInterview(interview, listItems, enabled): Promise<void> {
    try {
      enabled = true;
      await this._adminService.adminSetUpInterview(interview.id);
      this._sharedService.sidebarAdminBadges.interviewPending++;
      this._sharedService.sidebarAdminBadges.interviewSetUp--;
      this._toastr.success('Interview has been set up');
      const index = listItems.indexOf(interview);
      listItems.splice(index, 1);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Select change router
   * @param url
   */
  public routerApplicants(url): void {
    this._router.navigate([url]);
  }

}
